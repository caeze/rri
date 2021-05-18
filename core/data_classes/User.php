<?php
class User {
    private $ID;
    private $username;
    private $passwordHash;
    private $role;
    private $status;
    private $lastLoggedIn;
    private $language;
    private $comment;

    function __construct($ID, $username, $passwordHash, $role, $status, $lastLoggedIn, $language, $comment) {
        $this->ID = $ID;
        $this->username = $username;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->status = $status;
        $this->lastLoggedIn = $lastLoggedIn;
        $this->language = $language;
        $this->comment = $comment;
    }
    
    public function getID(){
        return $this->ID;
    }

    public function setID($ID){
        $this->ID = $ID;
    }

    public function getUsername(){
        return $this->username;
    }

    public function setUsername($username){
        $this->username = $username;
    }

    public function getPasswordHash(){
        return $this->passwordHash;
    }

    public function setPasswordHash($passwordHash){
        $this->passwordHash = $passwordHash;
    }

    public function getRole(){
        return $this->role;
    }

    public function setRole($role){
        $this->role = $role;
    }

    public function getStatus(){
        return $this->status;
    }

    public function setStatus($status){
        $this->status = $status;
    }

    public function getLastLoggedIn(){
        return $this->lastLoggedIn;
    }

    public function setLastLoggedIn($lastLoggedIn){
        $this->lastLoggedIn = $lastLoggedIn;
    }

    public function getLanguage(){
        return $this->language;
    }

    public function setLanguage($language){
        $this->language = $language;
    }

    public function getComment(){
        return $this->comment;
    }

    public function setComment($comment){
        $this->comment = $comment;
    }
}
?>
