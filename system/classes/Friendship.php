<?php

namespace Mozg\classes;

class Friendship extends Module
{
    /** @var int user ID */
    public int $user_id;

    /**
     * @param int $user_id
     */
    public function __construct(int $user_id)
    {
        $this->user_id = $user_id;
    }
    
    /**
     * Summary of CheckFriends
     * @param mixed $friend_id
     * @return bool
     */
    function checkFriends(mixed $friend_id): bool
    {
        try{
            $users_list = unserialize(Cache::mozgCache("user_{$this->user_id}/friends"));
        }catch(\Exception $e){
            return false;
        }
        if(is_array($users_list)){
            return in_array($friend_id, $users_list);
        }else{
            return false;
        }        
    }

    /**
     * Summary of addFriend
     * @param int $friend_id
     * @return bool
     */
    public function addFriend(int $friend_id): bool
    {
        $users_list = unserialize(Cache::mozgCache("user_{$this->user_id}/friends"));
        $users_list[] = $friend_id;
        Cache::mozgCreateCache("user_{$this->user_id}/friends", serialize($users_list));

        $users_list = unserialize(Cache::mozgCache("user_{$friend_id}/friends"));
        $users_list[] = $this->user_id;
        Cache::mozgCreateCache("user_{$friend_id}/friends", serialize($users_list));
        return true;
    }

    /**
     * Summary of removeFriend
     * @param int $friend_id
     * @return bool
     */
    public function removeFriend(int $friend_id) : bool 
    {
        $users_list = unserialize(Cache::mozgCache("user_{$this->user_id}/friends"));
        $friend_key = array_search($friend_id, $users_list);
        unlink($users_list[$friend_key]);
        Cache::mozgCreateCache("user_{$this->user_id}/friends", serialize($users_list));

        $openTakeList = unserialize(Cache::mozgCache("user_{$friend_id}/friends"));
        $friend_key = array_search($this->user_id, $users_list);
        unlink($users_list[$friend_key]);
        Cache::mozgCreateCache("user_{$friend_id}/friends", serialize($openTakeList));
        return true;
    }

    /**
     * Summary of CheckBlackList
     * @param mixed $friend_id
     * @return bool
     */
    function checkBlackList(int $friend_id): bool
    {
        $users_list = unserialize(Cache::mozgCache("user_{$friend_id}/blacklist"));
        return in_array($this->user_id, $users_list);
    }

    public function addBlackList(int $friend_id): bool
    {
        $users_list = unserialize(Cache::mozgCache("user_{$this->user_id}/blacklist"));
        $users_list[] = $friend_id;
        Cache::mozgCreateCache("user_{$this->user_id}/friends", serialize($users_list));
        return true;
    }

    public function removeBlackList(int $friend_id): bool
    {
        $users_list = unserialize(Cache::mozgCache("user_{$this->user_id}/blacklist"));
        $friend_key = array_search($friend_id, $users_list);
        unlink($users_list[$friend_key]);
        Cache::mozgCreateCache("user_{$this->user_id}/blacklist", serialize($users_list));
        return true;
    }

}

