<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlanRolePermission
 */
#[ORM\Table(name: 'plan_role_permission')]
#[ORM\Entity(repositoryClass: 'App\Repository\PlanRolePermissionRepository')]
class PlanRolePermission extends BaseEntity
{
    /**
     * PlanRolePermission constructor.
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->plan = $fields['plan'];
        $this->role = $fields['role'];
        $this->permission = $fields['permission'];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data['plan'] = $this->getPlan()->toArray();
        $data['role'] = $this->getRole()->toArray();
        $data['permission'] = $this->getPermission()->toArray();
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var Role
     */
    #[ORM\ManyToOne(targetEntity: 'Role')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', nullable: true)]
    private $role;

    /**
     * @var Plan
     */
    #[ORM\ManyToOne(targetEntity: 'Plan')]
    #[ORM\JoinColumn(name: 'plan_id', referencedColumnName: 'id', nullable: true)]
    private $plan;

    /**
     * @var Permission
     */
    #[ORM\ManyToOne(targetEntity: 'Permission')]
    #[ORM\JoinColumn(name: 'permission_id', referencedColumnName: 'id', onDelete: 'cascade')]
    private $permission;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Role $role
     * @return $this
     */
    public function setRole(?Role $role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Role
     */
    public function getRole(): ?Role
    {
        return $this->role;
    }

    /**
     * @param Plan $plan
     * @return $this
     */
    public function setPlan(Plan $plan)
    {
        $this->plan = $plan;

        return $this;
    }

    /**
     * @return Plan
     */
    public function getPlan(): Plan
    {
        return $this->plan;
    }

    /**
     * @param Permission $permission
     * @return $this
     */
    public function setPermission(Permission $permission)
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * @return Permission
     */
    public function getPermission(): Permission
    {
        return $this->permission;
    }

    /**
     * @return string
     */
    public function getPermissionName(): string
    {
        return $this->permission->getName();
    }
}
