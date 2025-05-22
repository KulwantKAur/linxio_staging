<?php

namespace App\Service\PlatformSetting;

use App\Entity\Currency;
use App\Entity\PlatformSetting;
use App\Entity\Theme;
use App\Exceptions\ValidationException;
use App\Util\StringHelper;

trait PlatformSettingServiceFieldsTrait
{
    private function prepareCreateData(array $data, ?PlatformSetting $platformSetting = null): array
    {
        $errors = [];

        if (isset($data['logo'])) {
            if (StringHelper::isNullString($data['logo'])) {
                $data['logo'] = null;
            } else {
                $data['logo'] = $this->fileService->uploadResellerLogo($data['logo']);
            }
        } elseif (is_null($data['logo'])) {
            unset($data['logo']);
        }

        if (isset($data['favicon'])) {
            if (!$data['favicon']) {
                unset($data['favicon']);
            }
            if (StringHelper::isNullString($data['favicon'])) {
                $data['favicon'] = null;
            } else {
                $data['favicon'] = $this->fileService->uploadResellerLogo($data['favicon']);
            }
        } elseif (is_null($data['favicon'])) {
            unset($data['favicon']);
        }
        if (isset($data['clientDefaultThemeId']) && $data['clientDefaultThemeId']) {
            $theme = $this->em->getRepository(Theme::class)->find($data['clientDefaultThemeId']);
            $data['clientDefaultTheme'] = $theme;
        }

        if (isset($data['domain'])) {
            /** @var PlatformSetting $ps */
            $ps = $this->em->getRepository(PlatformSetting::class)->getByDomain($data['domain']);
            if ($ps && (($platformSetting && $platformSetting->getId() !== $ps->getId()) || !$platformSetting)) {
                $errors['domain'] = ['wrong_value' => $this->translator->trans('validation.errors.field.unique')];
            }
        }

        if ($data['currencyId'] ?? null) {
            $data['currency'] = $this->em->getRepository(Currency::class)->find($data['currencyId']);
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }

        return $data;
    }
}