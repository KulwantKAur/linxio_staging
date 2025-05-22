<?php

namespace App\Service\Reseller;

use App\Entity\Note;
use App\Entity\Reseller;
use App\Entity\Theme;
use App\Entity\User;
use App\Util\StringHelper;

trait ResellerServiceFieldsTrait
{
    private function prepareResellerData(array $data): array
    {
        if (isset($data['keyContact'])) {
            if (StringHelper::isNullString($data['keyContact'])) {
                $data['keyContact'] = null;
            } else {
                $data['keyContact'] = $this->em->getRepository(User::class)->find($data['keyContact']);

                if (!$data['keyContact']) {
                    unset($data['keyContact']);
                }
            }
        }

        if (isset($data['status']) && !in_array($data['status'], Reseller::STATUSES)) {
            throw new \Exception($this->translator->trans('reseller.wrongStatus'));
        }

        if (isset($data['taxNr']) && !is_numeric($data['taxNr'])) {
            $errors['taxNr'] = ['wrong_format' => $this->translator->trans('validation.errors.field.wrong_format')];
        }

        if (isset($data['managerId']) && $data['managerId']) {
            $manager = $this->em->getRepository(User::class)->find($data['managerId']);
            $data['manager'] = $manager;
        } elseif (isset($data['managerId']) && !$data['managerId']) {
            $data['manager'] = null;
        }

        if (isset($data['salesManagerId']) && $data['salesManagerId']) {
            $manager = $this->em->getRepository(User::class)->find($data['salesManagerId']);
            $data['salesManager'] = $manager;
        } elseif (isset($data['salesManagerId']) && !$data['salesManagerId']) {
            $data['salesManager'] = null;
        }

        if (isset($data['themeId']) && $data['themeId']) {
            $theme = $this->em->getRepository(Theme::class)->find($data['themeId']);
            $data['theme'] = $theme;
        }

        return $data;
    }

    private static function handleStatusParams(array $params)
    {
        if (isset($params['status'])) {
            $params['status'] = $params['status'] === Reseller::STATUS_ALL ? Reseller::STATUSES : $params['status'];
        } else {
            $params['status'] = Reseller::LIST_STATUSES;
        }

        if (isset($params['showDeleted'])) {
            if (is_array($params['status'])) {
                $params['status'][] = Reseller::STATUS_DELETED;
            } else {
                $status = $params['status'];
                $params['status'] = [$status, Reseller::STATUS_DELETED];
            }
        } elseif (
            is_array($params['status']) && ($key = array_search(Reseller::STATUS_DELETED, $params['status'])) !== false
        ) {
            unset($params['status'][$key]);
        } elseif (!is_array($params['status']) && $params['status'] === Reseller::STATUS_DELETED) {
            $params['status'] = '';
        }

        return $params;
    }

    public function handleResellerNotes(array $data, Reseller $reseller, User $currentUser)
    {
        if ($data['resellerNote'] ?? null) {
            $this->noteService->create(
                [
                    'note' => $data['resellerNote'],
                    'reseller' => $reseller,
                    'noteType' => Note::TYPE_RESELLER,
                    'createdBy' => $currentUser
                ]
            );
        }
        if (!$currentUser->isInResellerTeam() && ($data['adminNote'] ?? null)) {
            $this->noteService->create(
                [
                    'note' => $data['adminNote'],
                    'reseller' => $reseller,
                    'noteType' => Note::TYPE_ADMIN,
                    'createdBy' => $currentUser
                ]
            );
        }
    }
}