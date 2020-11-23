<?php 
namespace frontend\controllers;

use frontend\components\FrontendController;
use Yii;
use yii\base\Model;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use frontend\models\NewsModel;
use common\models\Posts;
use common\models\Sharefb;
use frontend\models\SignupForm;
use frontend\models\LoginForm;
use Facebook\Facebook;
use frontend\models\Members;
use frontend\models\TrackingModel;
use frontend\models\GlobalModel;
use frontend\models\MinigameModel;
use frontend\models\MinigameSharefb;
use frontend\models\SystemConfigModel;
use frontend\models\BiasRandom;
use frontend\models\GiftcodeModel;
use frontend\models\MinigameVongxoayReward;
use frontend\models\MinigameVongXoayModel;
class MinigamevongxoayController extends Controller
{
	public function beforeAction($action) 
	{ 
		$this->enableCsrfValidation = false; 
		return parent::beforeAction($action); 
	}
	
	public static function jsonOut($error, $msg = null, $data = []) {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->data = [
            'error' => $error, 
            'msg' 	=> $msg,
            'data' 	=> $data,
        ];
        return $response;
    }
	
	/** Lắc xí ngầu **/
	public function actionXoay()
	{
		try{
			
			if(Yii::$app->user->isGuest){
				return $this->jsonOut(true, 'fail','Vui lòng đăng nhập!');
			}
			$time_now 	= time();
			$time_event 	= strtotime("2020-11-01");
			$time_endevent 	= strtotime("2020-12-07");
			if($time_event > $time_now){
				return $this->jsonOut(true, 'fail','Sự kiện chưa bắt đầu!');
			}
			
			if($time_endevent < $time_now){
				return $this->jsonOut(true, 'fail','Sự kiện đã kết thúc!');
			}


			$minigameModel 	= new MinigameVongXoayModel();
			
			$member_id 		=  Yii::$app->user->identity->member_id;
			//
			$luotchoiconlai = $minigameModel->countLuotChoi($member_id);
			if($luotchoiconlai > 0){
				//lay danh sach phan thuong
				$phanthuong = $minigameModel->getPhanThuongVongXoay();

				$data = array();
				foreach($phanthuong as $rs){
					$data[$rs->id] = $rs->percent;
				}
				//print_r($data);
				/** random percent result **/
				$randomModel = new BiasRandom();
				$randomModel->setData($data);
				$result 	= $randomModel->random();
				$reward_id 	= $result[0];
				
				/** get phan thuong **/
				$minigameModel 		= new MinigameVongXoayModel();
				$member_point_begin = $minigameModel->getDiemByMemberId($member_id);
				$member_point 		= (int)$member_point_begin;	
				$point 				= 0;
				
				if($reward_id == 1){
					$point = 1;
					$images = 'style/teaser/images/ngoc1.png';
					$name   = 'Ngoc rong I'; // ngoc rong 1
				}elseif($reward_id == 2){
					$point = 2; 
					$images = 'style/teaser/images/ngoc2.png';
					$name   = 'Ngoc rong II';// ngoc rong 2
				}elseif($reward_id == 3){
					$point = 3;
					$images = 'style/teaser/images/ngoc3.png'; 
					$name   = 'Ngoc rong III';// ngoc rong 3
				}elseif($reward_id == 4){
					$point = 4;
					$images = 'style/teaser/images/ngoc4.png';
					$name   = 'Ngoc rong IV';// ngoc rong 4
				}elseif($reward_id == 5){
					$point = 5; 
					$images = 'style/teaser/images/ngoc5.png';
					$name   = 'Ngoc rong V';// ngoc rong 5
				}elseif($reward_id == 6){
					$point = 6;
					$images = 'style/teaser/images/ngoc6.png';
					$name   = 'Ngoc rong VI'; // ngoc rong 6
				}elseif($reward_id == 7){
					$point = 7;
					$images = 'style/teaser/images/ngoc7.png';
					$name   = 'Ngoc rong VII'; // ngoc rong 7
				}
				//luu phan thuong vao database
				$flag_save 		= $minigameModel->AddVongxoayOutcome($member_id,$reward_id,$point,$images,$name);
				if($flag_save){

					//lay diem hien tai, luot choi hien tai cua user
					$member_point_end 	= $minigameModel->getDiemByMemberId($member_id);
					$member_point 		= (int)$member_point_end;	
					$luotchoiconlai 	= $minigameModel->countLuotChoi($member_id);
					
					
			
					return $this->jsonOut(false, 'success'.$luotchoiconlai,[
						'outcome' 		=> $reward_id,
						'member_point' 	=> $member_point,
						'luotchoiconlai'=> $luotchoiconlai,
					
					]);
				}else{
					return $this->jsonOut(true, 'fail','Có lỗi xảy ra vui lòng liên hệ Admin hỗ trợ!');
				}
			}else{
				return $this->jsonOut(true, 'fail'.$luotchoiconlai,'Bạn đã hết lượt chơi!');
			}
		}catch(\Exception $e){
			return $this->jsonOut(true, 'fail',$e->getMessage());
		}
	}
	
	public function actionDoicode()
	{
		$codeid	= crequest()->post('codeid');
		if(Yii::$app->user->isGuest){
			return $this->jsonOut(true, 'fail','Vui lòng đăng nhập!');
		}
		$member_id 			=  Yii::$app->user->identity->member_id;
		$minigameModel 		= new MinigameVongXoayModel();
		$result_member_longphuonghoang = $minigameModel->getDiemByMemberId($member_id);
		$result_member_longphuonghoang = (int)$result_member_longphuonghoang;
		$code = "";	
		if(!empty($codeid)){
			switch($codeid){
				case 1:
					$model 	= new GiftcodeModel();
					$code_phuonghoangsocap 		= 5001;
					$code_phuonghoangsocap_nhan = $model->checkMemberIssetGiftcode($member_id,$code_phuonghoangsocap);
					if(!empty($code_phuonghoangsocap_nhan)){
						$code = $code_phuonghoangsocap_nhan->giftcode;
					}
					elseif($result_member_longphuonghoang>=5)
					{
						/** Code Pháp Sư Sơ Cấp **/
						
						if(empty($code_phuonghoangsocap_nhan)){
							$code_phuonghoangsocap_nhan = $model->getActiveGiftcode($code_phuonghoangsocap);
							if(!empty($code_phuonghoangsocap_nhan)){
								$model->updateGiftcode($code_phuonghoangsocap_nhan->id,$member_id);
								$flag_buy_hero = $minigameModel->addBuyHero($member_id,1,5);
							}
						}
						if(!empty($code_phuonghoangsocap_nhan)){
							$code = $code_phuonghoangsocap_nhan->giftcode;					
						}else{
							$code = "Giftcode đã phát hết!";
						}
					}else{
						$code = "Linh sư chưa đủ điểm nhân code";
					}
					break;
				case 2:
					$code_phuonghoangtrungcap 	= 5002;
					$model 	= new GiftcodeModel();
					$code_phuonghoangtrungcap_nhan 	= $model->checkMemberIssetGiftcode($member_id,$code_phuonghoangtrungcap);
					if(!empty($code_phuonghoangtrungcap_nhan)){
						$code = $code_phuonghoangtrungcap_nhan->giftcode;
					}elseif($result_member_longphuonghoang >= 35 ){
						/** Code Pháp Sư Trung Cấp **/
						if(empty($code_phuonghoangtrungcap_nhan)){
							$code_phuonghoangtrungcap_nhan = $model->getActiveGiftcode($code_phuonghoangtrungcap);
							if(!empty($code_phuonghoangtrungcap_nhan)){
								$model->updateGiftcode($code_phuonghoangtrungcap_nhan->id,$member_id);
								$flag_buy_hero = $minigameModel->addBuyHero($member_id,1,35);
							}
						}
						if(!empty($code_phuonghoangtrungcap_nhan)){
							$code = $code_phuonghoangtrungcap_nhan->giftcode;
							
						}else{
							$code = "Giftcode đã phát hết!";
						}
						
					}else{
						$code = "Linh sư chưa đủ điểm nhân code";
					}
					break;
				case 3:
					$code_phuonghoangcaocap 	= 5003;
					$model 	= new GiftcodeModel();
					$code_phuonghoangcaocap_nhan 	= $model->checkMemberIssetGiftcode($member_id,$code_phuonghoangcaocap);
					if(!empty($code_phuonghoangcaocap_nhan)){
						$code = $code_phuonghoangcaocap_nhan->giftcode;
					}elseif($result_member_longphuonghoang>=95){
						/** Code Pháp Sư Cao Cấp **/
						
						if(empty($code_phuonghoangcaocap_nhan)){
							$code_phuonghoangcaocap_nhan = $model->getActiveGiftcode($code_phuonghoangcaocap);
							if(!empty($code_phuonghoangcaocap_nhan)){
								$model->updateGiftcode($code_phuonghoangcaocap_nhan->id,$member_id);
								$flag_buy_hero = $minigameModel->addBuyHero($member_id,1,95);
							}
						}
						if(!empty($code_phuonghoangcaocap_nhan)){
							$code = $code_phuonghoangcaocap_nhan->giftcode;
														
						}else{
							$code = "Giftcode đã phát hết!";
						}
					}else{
						$code = "Linh sư chưa đủ điểm nhân code";
					}
					break;	
				case 4:
					$code_phuonghoangsieucap 	= 5004;
					$model 	= new GiftcodeModel();
					$code_phuonghoangsieucap_nhan 	= $model->checkMemberIssetGiftcode($member_id,$code_phuonghoangsieucap);
					if(!empty($code_phuonghoangsieucap_nhan)){
						$code = $code_phuonghoangsieucap_nhan->giftcode;
					}elseif($result_member_longphuonghoang>=215){
						/** Code Pháp Sư Vô Thượng **/
						if(empty($code_phuonghoangsieucap_nhan)){
							$code_phuonghoangsieucap_nhan = $model->getActiveGiftcode($code_phuonghoangsieucap);
							if(!empty($code_phuonghoangsieucap_nhan)){
								$model->updateGiftcode($code_phuonghoangsieucap_nhan->id,$member_id);
								$flag_buy_hero = $minigameModel->addBuyHero($member_id,1,215);
							}
						}
						if(!empty($code_phuonghoangsieucap_nhan)){
							$code = $code_phuonghoangsieucap_nhan->giftcode;				
						}else{
							$code = "Giftcode đã phát hết!";
						}
					}else{
						$code = "Linh sư chưa đủ điểm nhân code";
					}
					break;
				default:
					$code = "Có lỗi xảy ra vui lòng liên hệ Admin!";
			}
			$member_point	= $minigameModel->getDiemByMemberId($member_id);
			$member_point	= (int)$member_point;
			$result_member_longphuonghoang 	= $minigameModel->getLongPhuongHoangByMemberId($member_id);
			$member_longphuonghoang 		= (int)$result_member_longphuonghoang;	
			return $this->jsonOut(false, 'success',[
				'code'=>$code,
				'member_point'=>$member_point,
				'member_longphuonghoang'=>$member_longphuonghoang,
				]);
		}
		else{
			return $this->jsonOut(true, 'fail','Có lỗi xảy ra vui lòng liên hệ Admin!');
		}
	}
	
}
?>