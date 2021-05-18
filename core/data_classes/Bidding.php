<?php
class Bidding {
    private $ID;
    private $articleID;
    private $biddingUserID;
    private $date;
    private $amount;

    function __construct($ID, $articleID, $biddingUserID, $date, $amount) {
        $this->ID = $ID;
        $this->articleID = $articleID;
        $this->biddingUserID = $biddingUserID;
        $this->date = $date;
        $this->amount = $amount;
    }
    
    public function getID(){
        return $this->ID;
    }

    public function setID($ID){
        $this->ID = $ID;
    }

    public function getArticleID(){
        return $this->articleID;
    }

    public function setArticleID($articleID){
        $this->articleID = $articleID;
    }

    public function getBiddingUserID(){
        return $this->biddingUserID;
    }

    public function setBiddingUserID($biddingUserID){
        $this->biddingUserID = $biddingUserID;
    }

    public function getDate(){
        return $this->date;
    }

    public function setDate($date){
        $this->date = $date;
    }

    public function getAmount(){
        return $this->amount;
    }

    public function setAmount($amount){
        $this->amount = $amount;
    }
}
?>
