<?php 
namespace frontend\widget;

use yii\base\Widget;
use Yii;
use frontend\models\MinigameModel;
use frontend\models\MinigameVongXoayModel;
class DieuUocRongThanWidget extends Widget
{
	public $member_id;
	public function run()
	{
		$view 			= 'dieuuocrongthan';
		$dataRender 	= array();
		$minigameModel 	= new MinigameVongXoayModel();
		$num_luotchoi 	= 0;
		if(!empty($this->member_id)){
			
			$flag_logintoday	   		= $minigameModel->checkIssetFirstLoginTotay($this->member_id);
			if(!$flag_logintoday){	
				$result_add_logintoday 	= $minigameModel->addFirstLoginToday($this->member_id);
			}
			
			$num_luotchoi 			= $minigameModel->countLuotChoi($this->member_id);
      
		}
		
		return $this->render($view,[
			'num_luotchoi' => $num_luotchoi,
			
			
		]);
	}
}

?>