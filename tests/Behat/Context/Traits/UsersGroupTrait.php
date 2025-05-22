<?php

namespace App\Tests\Behat\Context\Traits;

trait UsersGroupTrait
{
    protected $userGroup;

    /**
     * @When I want create user group
     */
    public function iWantCreateUserGroup()
    {
        $response = $this->post('/api/user-groups', $this->fillData);
        $this->userGroup = json_decode($response->getResponse()->getContent());
    }

    /**
     * @When I want edit saved user group
     */
    public function iWantEditSavedUserGroup()
    {
        $this->patch('/api/user-groups/' . $this->userGroup->id, $this->fillData);
    }

    /**
     * @When I want delete saved user group
     */
    public function iWantDeleteSavedUserGroup()
    {
        $this->delete('/api/user-groups/' . $this->userGroup->id);
    }

    /**
     * @When I want get saved user group by id
     */
    public function iWantGetSavedUserGroupById()
    {
        $this->get('/api/user-groups/' . $this->userGroup->id);
    }

    /**
     * @When I want get user group list
     */
    public function iWantGetUserGroupList()
    {
        $this->get('/api/user-groups');
    }

    /**
     * @When I want add current user to userIds
     */
    public function iWantAddCurrentUserToUserIds()
    {
        $this->fillData['userIds'] = [$this->authorizedUser->getId()];
    }
}
