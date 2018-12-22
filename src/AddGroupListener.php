<?php 

namespace Helmgast\Auth\Auth0;

use Flarum\User\Event\Registered;
use Flarum\Group\Group;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Exception;

class AddGroupListener
{
    /**
     * @var SettingsRepository
     */
    protected $settings;
    /**
     * @var int
     */
    protected $defaultGroup;
    public function __construct(SettingsRepositoryInterface $settings) {
        $this->settings = $settings;
        $this->defaultGroup = 5;
    }
    /**
     * Subscribe to event dispatcher
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Registered::class, [$this, 'addGroup']);
    }
    /**
     * Attaches the default group to the activated user
     * @param Registered $event
     */
    public function addGroup(Registered $event) {
        if($this->defaultGroup == Group::MEMBER_ID) {
            return;
        }
        $event->user->groups()->attach($this->defaultGroup);
    }
}