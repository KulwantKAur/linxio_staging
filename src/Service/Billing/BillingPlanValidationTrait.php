<?php

namespace App\Service\Billing;

use App\Entity\BaseEntity;
use App\Entity\BillingPlan;
use App\Exceptions\ValidationException;

trait BillingPlanValidationTrait
{
    public function validateCreateFields(array $fields)
    {
        $errors = [];

        if (!($fields['name'] ?? null)) {
            $errors['name'] = ['required' => $this->translator->trans('validation.errors.field.required')];
        }

        if ($this->em->getRepository(BillingPlan::class)->findOneBy(['name' => $fields['name']])) {
            $errors['name'] = ['required' => $this->translator->trans('entities.already_exist')];
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    public function validateEditFields(array $fields, BillingPlan $billingPlan)
    {
        $errors = [];

        if (isset($fields['name'])) {
            $billingPlanByName = $this->em->getRepository(BillingPlan::class)->findOneBy([
                'name' => $fields['name'],
                'status' => BaseEntity::STATUS_ACTIVE
            ]);
            if ($billingPlanByName && $billingPlan->getId() !== $billingPlanByName->getId()) {
                $errors['name'] = ['required' => $this->translator->trans('entities.already_exist')];
            }
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }
}