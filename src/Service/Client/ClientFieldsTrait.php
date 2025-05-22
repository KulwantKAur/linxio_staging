<?php


namespace App\Service\Client;


use App\Entity\BillingPlan;
use App\Entity\Client;
use App\Entity\Notification\TemplateSet;
use App\Entity\Plan;
use App\Entity\Role;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\User;
use App\Exceptions\ValidationException;
use App\Service\User\UserService;
use App\Service\Validation\ValidationService;
use App\Util\StringHelper;

trait ClientFieldsTrait
{
    public function handleNotesFields(array $data, Client $client, User $currentUser)
    {
        if ($data['clientNote'] ?? null) {
            $this->noteService->create(
                [
                    'note' => $data['clientNote'],
                    'client' => $client,
                    'noteType' => Team::TEAM_CLIENT,
                    'createdBy' => $currentUser
                ]
            );
        }
        if (!$currentUser->isInClientTeam() && ($data['adminNote'] ?? null)) {
            $this->noteService->create(
                [
                    'note' => $data['adminNote'],
                    'client' => $client,
                    'noteType' => Team::TEAM_ADMIN,
                    'createdBy' => $currentUser
                ]
            );
        }

        return $data;
    }

    /**
     * @param array $params
     * @return array
     */
    public function prepareEditParams(array $params, User $currentUser): array
    {
        if (!isset($params['status']) || !$params['status']) {
            $params['status'] = Client::STATUS_CLIENT;
        }

        if (isset($params['accountingContact']) && $params['accountingContact']) {
            $accountingContact = $this->em->getRepository(User::class)->find($params['accountingContact']);
            $params['accountingContact'] = $accountingContact;
        }

        if (isset($params['keyContactId']) && $params['keyContactId']) {
            $keyContact = $this->em->getRepository(User::class)->find($params['keyContactId']);
            $params['keyContact'] = $keyContact;
        }

        if (isset($params['expirationDate']) && $params['expirationDate']) {
            $params['expirationDate'] = self::parseDateToUTC($params['expirationDate']);
        }

        return $params;
    }


    /**
     * @param array $params
     * @return array
     */
    public function prepareCreateParams(array $params): array
    {
        if (!isset($params['status']) || !$params['status']) {
            $params['status'] = Client::STATUS_CLIENT;
        }

        if ($params['manager'] ?? null) {
            $manager = $this->em->getRepository(User::class)->find($params['manager']);
            $params['manager'] = $manager;
        } else {
            unset($params['manager']);
        }

        if (isset($params['planId']) && $params['planId']) {
            $plan = $this->em->getRepository(Plan::class)->find($params['planId']);
            $params['plan'] = $plan;
        }

        if (isset($params['expirationDate']) && $params['expirationDate']) {
            $params['expirationDate'] = self::parseDateToUTC($params['expirationDate']);
        }

        return $params;
    }

    public function handlePlanIdField(array $params, Client $client, User $currentUser): Client
    {
        if (isset($params['planId']) && $params['planId']) {
            $plan = $this->em->getRepository(Plan::class)->find($params['planId']);

            if ($plan && $plan->getId() !== $client->getPlan()->getId()) {
                $client->setPlan($plan);
                $this->settingService->setSettingsArray(
                    $client->getTeam(),
                    Plan::PLAN_DEFAULT_SETTINGS[$plan->getName()],
                    $currentUser);
            }
        }

        return $client;
    }

    public function initClientSettings(Client $client, User $currentUser)
    {
        $settings = Plan::PLAN_DEFAULT_SETTINGS[$client->getPlan()->getName()];

        if ($currentUser->isInResellerTeam()) {
            if ($currentUser->getPlatformSettings()?->getClientDefaultTheme()) {
                $clientDefaultTheme = $currentUser->getPlatformSettings()->getClientDefaultTheme();
                $settings[] = [
                    'role' => null,
                    'name' => Setting::THEME_SETTING,
                    'value' => $clientDefaultTheme->getId()
                ];
            }

            $templateSet = $this->em->getRepository(TemplateSet::class)->getByTeam($currentUser->getTeam());
            $settings[] = [
                'role' => null,
                'name' => Setting::NOTIFICATION_TEMPLATE_SETTING,
                'value' => $templateSet->getId()
            ];
        }

        $this->settingService->setSettingsArray($client->getTeam(), $settings, $currentUser);
    }


    /**
     * @param array $params
     * @param User $currentUser
     * @param User $targetUser
     * @return array
     */
    private function prepareEditClientUserParams(array $params, User $currentUser, User $targetUser): array
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
    private function validateClientUserEditableFields(array $fields, User $user)
    {
        $errors = [];
        if (isset($fields['name']) && empty($fields['name'])) {
            $errors['name'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if (isset($fields['role']) && !($fields['role'] instanceof Role)) {
            $errors['roleId'] = ['required' => $this->translator->trans('validation.errors.field.wrong_value')];
        }

        if ($fields['driverId'] ?? null) {
            $userWithSameDriverId = $this->em->getRepository(User::class)
                ->findOneBy(['driverId' => $fields['driverId'], 'team' => $user->getTeam()]);
            if ($userWithSameDriverId && $user->getId() !== $userWithSameDriverId->getId()) {
                $errors['driverId'] = ['wrong_value' => $this->translator->trans('validation.errors.field.unique')];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param $fields
     * @param User $currentUser
     * @throws ValidationException
     */
    private function validateClientFields($fields, User $currentUser)
    {
        $errors = [];

        if (isset($fields['status']) && !in_array(strtolower($fields['status']), Client::ALLOWED_STATUSES, true)) {
            $errors['status'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
        }
        if (!$currentUser->isInClientTeam() && (isset($fields['planId']) && !is_numeric($fields['planId']))) {
            $errors['plan'] = ['wrong_value' => $this->translator->trans('validation.errors.field.wrong_value')];
        }
        if (Client::STATUS_DEMO === $fields['status'] && (!isset($fields['expirationDate']) || empty($fields['expirationDate']))) {
            $errors['expirationDate']['required'] = $this->translator->trans('validation.errors.field.required');
        }
        if ($fields['expirationDate'] ?? null) {
            $errors = $this->validationService->validateDate(
                $fields,
                'expirationDate',
                $errors,
                ValidationService::GREATER_THAN
            );
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }
}