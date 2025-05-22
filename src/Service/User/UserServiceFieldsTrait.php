<?php

namespace App\Service\User;

use App\Entity\Client;
use App\Entity\Notification\Event;
use App\Entity\Role;
use App\Entity\Route;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\User;
use App\Exceptions\ValidationException;
use App\Util\StringHelper;

trait UserServiceFieldsTrait
{
    /**
     * @param array $fields
     * @throws ValidationException
     */
    private function validateUserFields(array $fields)
    {
        $errors = [];
        if (!isset($fields['name']) || !$fields['name']) {
            $errors['name'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if (!isset($fields['email']) || !$fields['email']) {
            $errors['email'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if (!isset($fields['phone']) || !$fields['phone']) {
            $errors['phone'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if (!$this->isValidEmail($fields['email'])) {
            $errors['email'] = ['wrong_format' => $this->translator->trans('validation.errors.field.wrong_format')];
        }
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $fields['email']]);
        if ($user) {
            $errors['email'] = ['email_exist' => $this->translator->trans('validation.errors.field.email_exist')];
        }
        if (!isset($fields['teamType']) || !$fields['teamType']
            || !in_array(strtolower($fields['teamType']), Team::TEAM_TYPES)) {
            $errors['teamType'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
        }
        if ($fields['driverId'] ?? null) {
            $team = null;
            if ($fields['teamType'] === Team::TEAM_ADMIN) {
                $team = $this->getAdminTeam();
            } elseif ($fields['teamType'] === Team::TEAM_CLIENT && isset($fields['client'])) {
                $team = $fields['client']->getTeam();
            }
            if ($team) {
                $userWithSameDriverId = $this->em->getRepository(User::class)
                    ->findOneBy(['driverId' => $fields['driverId'], 'team' => $team]);
                if ($userWithSameDriverId) {
                    $errors['driverId'] = ['wrong_value' => $this->translator->trans('validation.errors.field.unique')];
                }
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param array $fields
     * @throws ValidationException
     */
    private function validateEditableFields(array $fields)
    {
        $errors = [];

        if (isset($fields['name']) && empty($fields['name'])) {
            $errors['name'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if (isset($fields['phone']) && empty($fields['phone'])) {
            $errors['phone'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }
        if (isset($fields['role']) && !($fields['role'] instanceof Role)) {
            $errors['roleId'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
        }
        if (isset($fields['driverRouteScope']) && !in_array($fields['driverRouteScope'], Route::getScopes())) {
            $errors['driverRouteScope'] = [
                'required' => $this->translator
                    ->trans('validation.errors.field.wrong_value')
            ];
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param array $params
     * @param array $allowedParams
     * @return array
     */
    private function prepareEditParams(array $params, array $allowedParams = []): array
    {
        $prepared = [];

        foreach ($params as $name => $val) {
            if (in_array($name, $allowedParams, true)) {
                $prepared[$name] = $val;
            }
        }

        if (isset($prepared['roleId'])) {
            $prepared['role'] = $this->em->getRepository(Role::class)->find($prepared['roleId']);
            unset($prepared['roleId']);
        }
        if (isset($prepared['allTeamsPermissions'])) {
            $prepared['allTeamsPermissions'] = StringHelper::stringToBool($prepared['allTeamsPermissions']);
        }

        return $prepared;
    }

    /**
     * @param string $email
     * @return bool
     */
    function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) && preg_match('/@.+\./', $email);
    }

    private function handleEditParams(User $user, array $userData): User
    {
        if ($user->isInAdminTeam() && isset($userData['allTeamsPermissions'])) {
            $user->setAllTeamsPermissions(StringHelper::stringToBool($userData['allTeamsPermissions']));
        }

        //check clients permissions if change user role
        if (isset($userData['role']) && $user->getRole()->getId() !== $userData['role']->getId()) {
            $this->checkUserIsClientManager($user);
            if ($user->isAccountManager() || $user->isSalesManager()) {
                $this->em->getRepository(Client::class)->deleteManagerFromClientsById($user->getId());
                $this->em->getRepository(Client::class)->deleteSalesManagerFromClientsById($user->getId());
            }
            $user->setRole($userData['role']);
        }

        if (isset($userData['teamPermissions'])) {
            $user = $this->updateTeamPermissions($user, $userData['teamPermissions'] ?? []);
        }

        if (isset($userData['phone']) && $user->getPhone() !== $userData['phone']) {
            $user->unverifyPhone();
        }

        if ($userData['avatar'] ?? null) {
            $picture = $this->fileService->uploadAvatar($userData['avatar']);
            $user->setPicture($picture);
        }

        if ($userData[Setting::LANGUAGE_SETTING] ?? null) {
            $languageSetting = $this->settingService
                ->setLanguageSetting($user->getTeam(), $userData[Setting::LANGUAGE_SETTING], $user, $user->getRole());
            $user->setLanguage($languageSetting->getValue());
        }

        return $user;
    }

    private function handleCreateParams(User $user, array $userData): User
    {
        if ($userData['avatar'] ?? null) {
            $picture = $this->fileService->uploadAvatar($userData['avatar']);
            $user->setPicture($picture);
        }
        if ($userData['roleId'] ?? null) {
            $role = $this->em->getRepository(Role::class)->find($userData['roleId']);
            if ($role) {
                $user->setRole($role);
            }
        }

        if ($userData['teamType'] === Team::TEAM_ADMIN) {
            $team = $this->getAdminTeam();
            $user->setTeam($team);
            if ($user->isControlAdmin() || $user->isInstaller() || $user->isSupport()) {
                $user->setAllTeamsPermissions(true);
            } elseif (isset($userData['allTeamsPermissions'])) {
                $user->setAllTeamsPermissions(StringHelper::stringToBool($userData['allTeamsPermissions']));
            }

            if (isset($userData['teamPermissions'])) {
                $user = $this->updateTeamPermissions($user, $userData['teamPermissions'] ?? []);
            }
        } elseif ($userData['teamType'] === Team::TEAM_CLIENT) {
            $user->setTeam($userData['client']->getTeam());
        }

        if ($userData['createdBy'] ?? null) {
            $user->setCreatedBy($userData['createdBy']);
        }
        if (isset($userData['driverSensorId']) && empty($userData['driverSensorId'])) {
            $user->setDriverSensorId(null);
        }
        if (isset($userData['driverFOBId']) && empty($userData['driverFOBId'])) {
            $user->setDriverFOBId(null);
        }

        return $user;
    }

    private function prepareEditUserParams(array $params, User $currentUser, User $targetUser): array
    {
        $prepared = [];

        $allowedParams = UserService::getEditableFieldsByUser($currentUser, $targetUser);

        foreach ($params as $name => $val) {
            if (in_array($name, $allowedParams, true)) {
                $prepared[$name] = $val;
            }
        }

        if (isset($prepared['roleId'])) {
            $prepared['role'] = $this->em->getRepository(Role::class)->find($prepared['roleId']);
            unset($prepared['roleId']);
        }

        return $prepared;
    }


    /**
     * @param array $fields
     * @param User $user
     * @throws ValidationException
     */
    private function validateUserEditableFields(array $fields, User $user)
    {
        $errors = [];
        if (isset($fields['name']) && empty($fields['name'])) {
            $errors['name'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if (isset($fields['role']) && !($fields['role'] instanceof Role)) {
            $errors['roleId'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param array $params
     * @param bool $withDualAccount
     * @return array
     */
    private function handleParamsForDriversAndDualAccounts(array $params, bool $withDualAccount): array
    {
        if ($withDualAccount) {
            $params['isInDriverList'] = true;
        } else {
            $params['role'] = Role::ROLE_CLIENT_DRIVER;
        }
        if (isset($params['isDualAccount'])) {
            $params['isDualAccount'] = boolval($params['isDualAccount']);
        }

        return $params;
    }

    /**
     * @param User $user
     * @param User $targetUser
     * @return mixed
     */
    public static function getEditableFieldsByUser(User $user, User $targetUser)
    {
        switch ($user->getTeamType()) {
            case Team::TEAM_CLIENT:
            {
                if ($user->getId() === $targetUser->getId()) {
                    return User::EDITABLE_FIELDS_BY_ROLE[Team::TEAM_CLIENT][User::ME];
                } else {
                    switch ($user->getRoleName()) {
                        case Role::ROLE_CLIENT_ADMIN:
                            return User::EDITABLE_FIELDS_BY_ROLE[Team::TEAM_CLIENT][Role::ROLE_CLIENT_ADMIN];
                        case Role::ROLE_MANAGER:
                            return User::EDITABLE_FIELDS_BY_ROLE[Team::TEAM_CLIENT][Role::ROLE_MANAGER];
                    }
                }
            }
            case Team::TEAM_ADMIN:
                return User::EDITABLE_FIELDS_BY_ROLE[Team::TEAM_ADMIN];
            case Team::TEAM_RESELLER:
                return User::EDITABLE_FIELDS_BY_ROLE[Team::TEAM_RESELLER];
        }

        return [];
    }

    /**
     * @param array $params
     * @return array
     */
    public static function handleRoleParams(array $params): array
    {
        if (isset($params['role'])) {
            switch ($params['role']) {
                case Role::ROLE_CLIENT_DRIVER:
                    unset($params['role']);
                    $params['isInDriverList'] = true;
                    break;
                case UserService::FILTER_ROLE_ADMIN_DRIVER:
                    $params['role'] = Role::ROLE_ADMIN;
                    $params['isDualAccount'] = true;
                    break;
                case UserService::FILTER_ROLE_MANAGER_DRIVER:
                    $params['role'] = Role::ROLE_MANAGER;
                    $params['isDualAccount'] = true;
                    break;
                default:
                    break;
            }
        }

        return $params;
    }
}