<?php
namespace PHPFusion;

class UserRelations {
    
    private static $friend_list = [];
    private static $requested_list = [];
    private static $requestor_list = [];
    
    public function cacheRelations() {
        // cache
    }
    
    
    public function getUserRelations( $key = NULL ) {
        $relations = [
            0 => 'Pending',
            1 => 'Accepted',
            2 => 'Declined',
            3 => 'Blocked'
        ];
        return $key === NULL ? $relations : ( isset( $relations[ $key ] ) ? $relations[ $key ] : NULL );
    }
    
    /**
     * Sending Friend Request
     *
     * @param int $request_user
     * @param int $target_user
     *
     * @return int
     */
    public function friendRequest( int $request_user, int $target_user ) {
        $action_param = $this->setUserRequest( $request_user, $target_user );
        if ( !dbcount( "(user_a)", DB_USER_RELATIONS, "`user_a`=:user_a AND `user_b`=:user_b", $action_param ) ) {
            dbquery( "INSERT INTO ".DB_USER_RELATIONS." (`user_a`,`user_b`,`relation_status`,`relation_action`, `relation_datestamp`) VALUES (:user_a, :user_b, :status, :action, :time)", [
                    ':status' => 0,
                    ':action' => $request_user,
                    ':time'   => TIME,
                ] + $action_param );
            
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Cancels sent Friend Request
     *
     * @param int $request_user
     * @param int $target_user
     *
     * @return bool
     */
    public function cancelFriendRequest( int $request_user, int $target_user ) {
        $action_param = $this->setUserRequest( $request_user, $target_user );
        if ( dbcount( "(user_a)", DB_USER_RELATIONS, "`user_a`=:user_a AND `user_b`=:user_b AND `relation_status`=0", $action_param ) ) {
            dbquery( "DELETE FROM ".DB_USER_RELATIONS." WHERE `user_a`=:user_a AND `user_b`=:user_b", $action_param );
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Unfriend Request
     *
     * @param int $request_user
     * @param int $target_user
     *
     * @return bool
     */
    public function unfriendRequest( int $request_user, int $target_user ) {
        $action_param = $this->setUserRequest( $request_user, $target_user );
        if ( dbcount( "(user_a)", DB_USER_RELATIONS, "`user_a`=:user_a AND `user_b`=:user_b AND `relation_status`=1", $action_param ) ) {
            dbquery( "DELETE FROM ".DB_USER_RELATIONS." WHERE `user_a`=:user_a AND `user_b`=:user_b", $action_param );
            return TRUE;
        }
        return FALSE;
    }
    
    
    /**
     * Request to Block User
     *
     * @param int $request_user
     * @param int $target_user
     *
     * @return bool
     */
    public function blockRequest( int $request_user, int $target_user ) {
        $action_param = $this->setUserRequest( $request_user, $target_user );
        if ( !dbcount( "(user_a)", DB_USER_RELATIONS, "user_a=:user_a AND user_b=:user_b AND relation_status=0", $action_param ) ) {
            dbquery( "INSERT INTO ".DB_USER_RELATIONS." (`user_a`, `user_b`, `relation_action`, `relation_status`, `relation_datestamp`) VALUES (:user_a, :user_b, :action, :status, :time)", [
                    ':status' => 3,
                    ':time'   => TIME,
                    ':action' => $request_user
                ] + $action_param );
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Request to Unblock User
     *
     * @param int $request_user
     * @param int $target_user
     *
     * @return bool
     */
    public function unblockRequest( int $request_user, int $target_user ) {
        $action_param = $this->setUserRequest( $request_user, $target_user );
        if ( dbcount( "(user_a)", DB_USER_RELATIONS, "`user_a`=:user_a AND `user_b`=:user_b AND `relation_status`=3", $action_param ) ) {
            dbquery( "DELETE FROM ".DB_USER_RELATIONS." WHERE `user_a`=:user_a AND `user_b`=:user_b AND relation_status=3", $action_param );
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Always make sure that user_a value is smaller than user_b
     *
     * @param int $user_a
     * @param int $user_b
     *
     * @return mixed
     */
    public function setUserRequest( int $user_a, int $user_b ) {
        $user[':user_a'] = $user_a;
        $user[':user_b'] = $user_b;
        if ( $user_a > $user_b ) {
            $user[':user_a'] = $user_b;
            $user[':user_b'] = $user_a;
        }
        if ( isnum( $user[':user_a'] ) && isnum( $user[':user_b'] ) ) {
            return $user;
        }
        return [];
    }
    
    /**
     * Accepting Friend Request
     *
     * @param $accept_user
     * @param $target_user
     *
     * @return bool
     */
    public function acceptFriendRequest( int $accept_user, int $target_user ) {
        $action_param = $this->setUserRequest( $accept_user, $target_user );
        if ( dbcount( "(user_a)", DB_USER_RELATIONS, "user_a=:user_a AND user_b=:user_b AND relation_status=0", $action_param ) ) {
            dbquery( "UPDATE ".DB_USER_RELATIONS." SET `relation_status`=:status,`relation_action`=:action, `relation_datestamp`=:time WHERE `user_a`=:user_a AND `user_b`=:user_b AND `relation_status`=0",
                [
                    ':status' => 1,
                    ':action' => $accept_user,
                    ':time'   => TIME,
                ] + $action_param );
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Check that these 2 users are friends
     * If the result returns a row, then the user are friends.
     *
     * @param $user_a
     * @param $user_b
     *
     * @return bool
     */
    public function checkUserFriendship( $user_a, $user_b ) {
        $action = $this->setUserRequest( $user_a, $user_b );
        return dbcount( "(user_a)", DB_USER_RELATIONS, "user_a=:index1 AND user_b=:index2 AND relation_status=1", [
            ':index1' => $action['user_a'],
            ':index2' => $action['user_b']
        ] );
    }
    
    /**
     * Friends List
     * Retrieve all the user's friends. Just provide a user id
     *
     * @param $user_id
     *
     * @return mixed
     */
    public function getUserFriends( $user_id ) {
        
        if ( empty( self::$friend_list[ $user_id ] ) ) {
            $result = dbquery( "SElECT IF('user_a=:index3', 'user_b', 'user_a') 'user_id', relation_datestamp FROM ".DB_USER_RELATIONS." WHERE ('user_a'=:index1 OR 'user_b'=:index2) AND relation_status=1", [
                ':index1' => $user_id,
                ':index2' => $user_id,
                ':index3' => $user_id,
            ] );
            if ( dbrows( $result ) ) {
                while ( $data = dbarray( $result ) ) {
                    self::$friend_list[ $user_id ][] = $data;
                }
            }
        }
        
        return self::$friend_list[ $user_id ];
    }
    
    /**
     * Pending Request List
     * Retrieve all the user request for the user from other users
     *
     * @param $user_id
     *
     * @return mixed
     */
    public function getUserRequests( $user_id ) {
        if ( empty( self::$requested_list[ $user_id ] ) ) {
            $result = dbquery( "SElECT IF('user_a=:index3', 'user_b', 'user_a') 'user_id', relation_datestamp FROM ".DB_USER_RELATIONS." WHERE ('user_a'=:index1 OR 'user_b'=:index2) AND relation_status=0 AND relation_action !=index4", [
                ':index1' => $user_id,
                ':index2' => $user_id,
                ':index3' => $user_id,
                ':index4' => $user_id
            ] );
            if ( dbrows( $result ) ) {
                while ( $data = dbarray( $result ) ) {
                    self::$requested_list[ $user_id ][] = $data;
                }
            }
        }
        
        return self::$requested_list[ $user_id ];
    }
    
    /**
     * Request sent by the user
     * Retrieve all the requests
     *
     * @param $user_id
     *
     * @return mixed
     */
    public function sentUserRequests( $user_id ) {
        if ( empty( self::$requestor_list[ $user_id ] ) ) {
            $result = dbquery( "SElECT IF('user_a=:index3', 'user_b', 'user_a') 'user_id', relation_datestamp FROM ".DB_USER_RELATIONS." WHERE ('user_a'=:index1 OR 'user_b'=:index2) AND relation_status=0 AND relation_action=index4", [
                ':index1' => $user_id,
                ':index2' => $user_id,
                ':index3' => $user_id,
                ':index4' => $user_id
            ] );
            if ( dbrows( $result ) ) {
                while ( $data = dbarray( $result ) ) {
                    self::$requestor_list[ $user_id ][] = $data;
                }
            }
        }
        
        return self::$requestor_list[ $user_id ];
    }
    
    /**
     * Get Friendship Data with the targetted $user_id
     *
     * @param $user_id
     *
     * @return array|null
     */
    public function getRelation( $user_id ) {
        if ( iMEMBER ) {
            $action_param = $this->setUserRequest( fusion_get_userdata( 'user_id' ), $user_id );
            $result = dbquery( "SELECT * FROM ".DB_USER_RELATIONS." WHERE user_a=:user_a AND user_b=:user_b", $action_param);
            if ( dbrows( $result ) ) {
                $data = dbarray( $result );
                return $data;
            }
        }
        return NULL;
    }
    
    
}
