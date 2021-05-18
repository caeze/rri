<?php
class Article {
    private $ID;
    private $status;
    private $addedByUserID;
    private $addedDate;
    private $remark;
    private $title;
    private $pictureFileName1;
    private $pictureFileName2;
    private $pictureFileName3;
    private $pictureFileName4;
    private $pictureFileName5;
    private $startingPrice;
    private $expiresOnDate;
    private $description;
    private $biddings;

    function __construct($ID, $status, $addedByUserID, $addedDate, $remark, $title, $pictureFileName1, $pictureFileName2, $pictureFileName3, $pictureFileName4, $pictureFileName5, $startingPrice, $expiresOnDate, $description, $biddings) {
        $this->ID = $ID;
        $this->status = $status;
        $this->addedByUserID = $addedByUserID;
        $this->addedDate = $addedDate;
        $this->remark = $remark;
        $this->title = $title;
        $this->pictureFileName1 = $pictureFileName1;
        $this->pictureFileName2 = $pictureFileName2;
        $this->pictureFileName3 = $pictureFileName3;
        $this->pictureFileName4 = $pictureFileName4;
        $this->pictureFileName5 = $pictureFileName5;
        $this->startingPrice = $startingPrice;
        $this->expiresOnDate = $expiresOnDate;
        $this->description = $description;
        $this->biddings = $biddings;
    }
    
    public function getID(){
        return $this->ID;
    }

    public function setID($ID){
        $this->ID = $ID;
    }

    public function getStatus(){
        return $this->status;
    }

    public function setStatus($status){
        $this->status = $status;
    }

    public function getAddedByUserID(){
        return $this->addedByUserID;
    }

    public function setAddedByUserID($addedByUserID){
        $this->addedByUserID = $addedByUserID;
    }

    public function getAddedDate(){
        return $this->addedDate;
    }

    public function setAddedDate($addedDate){
        $this->addedDate = $addedDate;
    }

    public function getRemark(){
        return $this->remark;
    }

    public function setRemark($remark){
        $this->remark = $remark;
    }

    public function getTitle(){
        return $this->title;
    }

    public function setTitle($title){
        $this->title = $title;
    }

    public function getPictureFileName1(){
        return $this->pictureFileName1;
    }

    public function setPictureFileName1($pictureFileName1){
        $this->pictureFileName1 = $pictureFileName1;
    }

    public function getPictureFileName2(){
        return $this->pictureFileName2;
    }

    public function setPictureFileName2($pictureFileName2){
        $this->pictureFileName2 = $pictureFileName2;
    }

    public function getPictureFileName3(){
        return $this->pictureFileName3;
    }

    public function setPictureFileName3($pictureFileName3){
        $this->pictureFileName3 = $pictureFileName3;
    }

    public function getPictureFileName4(){
        return $this->pictureFileName4;
    }

    public function setPictureFileName4($pictureFileName4){
        $this->pictureFileName4 = $pictureFileName4;
    }

    public function getPictureFileName5(){
        return $this->pictureFileName5;
    }

    public function setPictureFileName5($pictureFileName5){
        $this->pictureFileName5 = $pictureFileName5;
    }

    public function getStartingPrice(){
        return $this->startingPrice;
    }

    public function setStartingPrice($startingPrice){
        $this->startingPrice = $startingPrice;
    }

    public function getExpiresOnDate(){
        return $this->expiresOnDate;
    }

    public function setExpiresOnDate($expiresOnDate){
        $this->expiresOnDate = $expiresOnDate;
    }

    public function getDescription(){
        return $this->description;
    }

    public function setDescription($description){
        $this->description = $description;
    }

    public function getBiddings(){
        return $this->biddings;
    }

    public function setBiddings($biddings){
        $this->biddings = $biddings;
    }
}
?>
