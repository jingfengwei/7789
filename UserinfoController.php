<?php
header('content-type:text/html;charset=utf8');
// 指定允许其他域名访问  
header('Access-Control-Allow-Origin: *');  
// 响应类型  
header('Access-Control-Allow-Methods:POST');  
// 响应头设置  
header('Access-Control-Allow-Headers:x-requested-with,content-type');

require 'qiniuyunSDK/vendor/autoload.php';
use Qiniu\Auth;  
use Qiniu\Storage\UploadManager; 
$path = $_SERVER['DOCUMENT_ROOT'];
include($path."/protected/models/Message.php");
include($path."/protected/models/Doctor.php");
include($path."/protected/models/WechatUser.php");
include($path."/protected/models/WechatIntegralDetails.php");
include($path."/protected/models/Withdrawals.php");

date_default_timezone_set('PRC');
class UserinfoController extends Controller
{




    /**
     *个人中心  
     *{"openid":"ohACs0u9zN6Pe9lrCn1ibNuETSoA"}
     * 
     */
    
      public function actionUserInfo(){

          $post=file_get_contents('php://input');
          $data=json_decode($post,true);
          
        if (empty($data)){  
          	 $output = array('data'=>NULL, 'info'=>'你没有数据', 'code'=>101);
             exit(json_encode($output,JSON_UNESCAPED_UNICODE));
          }else{	
          	  
              $openid   = isset($data['openid'])?$data['openid']:'';
 			        $objCriteria = new CDbCriteria;
              $objCriteria->select = 'openid,id,headimgurl,username,occ_id,nickname,cell_phone';
              $objCriteria->addCondition("openid='$openid'"); 
              $wechat_user=WechatUser::model()->findAll($objCriteria);
       
              if (empty($wechat_user)) {
           	 		$output = array('info'=>'用户信息为空', 'code'=>102);
             		exit(json_encode($output,JSON_UNESCAPED_UNICODE));        
              }

              foreach ($wechat_user as $value) {

              		
		            		$user=array(
		            			'cell_phone'=>$value['cell_phone'],
		                  		'id'=>$value['id'],
		                  		'username'=>$value['username'],
		                  		'headimgurl'=>$value['headimgurl'],
		                  		'openid'=>$value['openid'],
		                  		'message'=>$this->messgae($value['id']),
		                      'occ_id'=>$value['occ_id'],
		                      'cell_phone'=>'',
		                      'nickname'=>base64_decode($value['nickname']),

		                  		);
  

            }   
                 	 		$output = array('data'=>$user,'info'=>'成功', 'code'=>200);
             		     exit(json_encode($output,JSON_UNESCAPED_UNICODE));    
          }

      }


    /**
     *我的消息(未读消息显示)
     * 
     */
    public function messgae($id='null'){ 
		    
    	 	 
    	$messgae=new Message();

    	return $count=$messgae->status($id);

  	}


  	/**
  	 *我的消息页面(更改已读消息状态)
  	 *{"id":"188"}
  	 */
  	
  	public function actionReadmessage(){
          $post=file_get_contents('php://input');
          $data=json_decode($post,true);
          

        if (empty($data)){ 
          	 $output = array('data'=>NULL, 'info'=>'你没有数据', 'code'=>101);
             exit(json_encode($output,JSON_UNESCAPED_UNICODE));
          }else{	
          	   $id   = isset($data['id'])?$data['id']:'';
          	
          	  $objCriteria = new CDbCriteria;
              $objCriteria->select = '*';
              $objCriteria->addCondition("user_id='$id'"); 
              $objCriteria->order ='create_time DESC' ;//排序条件
              $wechat_user=Message::model()->findAll($objCriteria);
	           if (empty($wechat_user)) { 
	          	 $output = array('info'=>'您暂时没有消息', 'code'=>102);
	             exit(json_encode($output,JSON_UNESCAPED_UNICODE));
	         	}

              $messgae=new Message();
              $my_message=$messgae->read($wechat_user);
         	
         	$output = array('data'=>$my_message,'info'=>'成功', 'code'=>200);
            exit(json_encode($output,JSON_UNESCAPED_UNICODE));    

          }
  	}


  	/**
  	 *个人中心 -我的权益(查看当前我的积分与零钱)
  	 * {"id":"188"}
  	 */
  	public function actionEquity(){

  		    $post=file_get_contents('php://input');
          $data=json_decode($post,true);
          

        if (empty($data)){ 
          	 $output = array('data'=>NULL, 'info'=>'你没有数据', 'code'=>101);
             exit(json_encode($output,JSON_UNESCAPED_UNICODE));
          }else{	
          	  $id   = isset($data['id'])?$data['id']:'';
 			  $objCriteria = new CDbCriteria;
              $objCriteria->select = 'id,balance,integral,openid';
              $objCriteria->addCondition("id='$id'"); 
              $wechat_user=WechatUser::model()->findAll($objCriteria);
              if (empty($wechat_user)) {
           	 		$output = array('info'=>'用户信息为空', 'code'=>102);
             		exit(json_encode($output,JSON_UNESCAPED_UNICODE));        
              }

   		      foreach ($wechat_user as $value) {     	
            	$user=array(
            		'id'=>$value['id'],
            		'balance'=>$value['balance'],
            		'integral'=>$value['integral'],
            		'openid'=>$value['openid'],
            		);
          	}
		   	 		$output = array('data'=>$user,'info'=>'成功', 'code'=>102);
             		exit(json_encode($output,JSON_UNESCAPED_UNICODE));  
     	}

  	}

  	/**
  	 *个人中心 -我的权益(积分)
  	 *{"id":"188","integral":"20"}
  	 */
  	public function actionIntegral(){

  		    $post=file_get_contents('php://input');
          $data=json_decode($post,true);
          

        if (empty($data)){ 
          	 		$output = array('data'=>NULL, 'info'=>'你没有数据', 'code'=>101);
             		exit(json_encode($output,JSON_UNESCAPED_UNICODE));
          }else{	

          	   $id   = isset($data['id'])?$data['id']:'';
          	   $integral   = isset($data['integral'])?$data['integral']:'';
          	   $integral=intval($integral);
    
          	   if ($id==''||$integral=='') {
           	 		$output = array('data'=>NULL, 'info'=>'没有数据', 'code'=>100);
             		exit(json_encode($output,JSON_UNESCAPED_UNICODE));         	   	
          	   }

          	   if ($integral<50) {
           	 		$output = array('data'=>NULL, 'info'=>'不能小于50积分', 'code'=>103);
             		exit(json_encode($output,JSON_UNESCAPED_UNICODE));  
          	   }
          	   		//获取当前用户总积分
          	   		$wechat_user=new WechatUser();
          	   		$num=$wechat_user->userIntegral($id);
          	   		$num=intval($num);
          	   		if ($num<$integral) {
	           	 		$output = array('info'=>'你的积分不足', 'code'=>104);
	             		exit(json_encode($output,JSON_UNESCAPED_UNICODE));        	   			
          	   		}
          	   		//当前积分-消费积分=剩余积分
          	   		$surplus=$num-$integral;
          	   		// $surplus=intval($surplus);
          	   		// $integral=intval($integral);
          	   		//添加积分数据
          	   		$time=date("Y-m-d H:i:s");

          	   try{

          	   		$dbTrans= Yii::app()->db->beginTransaction();

          	   		$WechatIntegralDetails=new WechatIntegralDetails();        
        					$WechatIntegralDetails->currentintegra=$num;
        					$WechatIntegralDetails->residualintegral=$surplus;
        					$WechatIntegralDetails->consumptionintegral=$integral;
        					$WechatIntegralDetails->user_id=$id; 
        					$WechatIntegralDetails->create_time=$time;
        					$WechatIntegralDetails->details='兑换积分'; 
        					$WechatIntegralDetails->status='0';   

					         //修改用户表中我的积分
                	$myIntegral=new WechatUser();
                	$count=$myIntegral->myIntegral($id,$surplus);

                	$money=$myIntegral->money($id,$integral);
					 if (!empty($count)&&$WechatIntegralDetails->save()&&!empty($money)) {
							
                                			  $dbTrans->commit();
                                $output = array('info'=>'兑换成功', 'code'=>200);
                                 exit(json_encode($output,JSON_UNESCAPED_UNICODE));   
                        }else{
                        
                            //抛出异常
                            throw new Exception('异常错误'); 
          
                        }


              }catch(Exception $e) {
                  print $e->getMessage();   
                  exit();         
              }

          }
  	}



 	


  	  /**
       *个人中心任务首页
       *{"openid":"ohACs0u9zN6Pe9lrCn1ibNuETSoA","type":"1"}
       * 用户uid  音视频区分值
       */
      public function actionUserwork(){
          $post=file_get_contents('php://input');
          $data=json_decode($post,true);
          
        if (empty($data)){ 
          $output = array('data'=>NULL, 'info'=>'你没有数据', 'code'=>101);
             exit(json_encode($output,JSON_UNESCAPED_UNICODE));
          }else{
            $openid   = isset($data['openid'])?$data['openid']:'';
            // $type     = isset($data['type'])?$data['type']:'';

              $objCriteria = new CDbCriteria;
              $objCriteria->select = 'id,openid,art_id,status,create_time,close_time,content,type';
              $objCriteria->addCondition("openid='$openid'"); 
              // $objCriteria->addCondition("type='$type'"); 
              $wechat_article_receive  =Articlereceive::model()->findAll($objCriteria);

              if(empty($wechat_article_receive)){
                     $output = array('info'=>'您没有任务', 'code'=>201);
                     exit(json_encode($output,JSON_UNESCAPED_UNICODE));
              }
              foreach ($wechat_article_receive as $key => $value) { 
                
                  if ($this->title($value['art_id'])!=''){
                        $article_receive[]=array(
                            'id' =>$value['id'],
                            'title' =>$this->title($value['art_id']),
                            'openid'=>$value['openid'],
                            'art_id'=>$value['art_id'],
                            'status'=>$this->status($value['status'],$value['content'],$value['type']),
                            'create_time'=>$value['create_time'],
                            'close_time'=>$value['close_time'],
                            'times'=>$this->times($value['create_time']),
                            'type'=>$value['type'],
                             'color'=>$this->collor($value['status'],$value['content'],$value['type']),

                          );               
                  }



              }
            $output = array('data'=>$article_receive, 'info'=>'成功', 'code'=>200);
            exit(json_encode($output,JSON_UNESCAPED_UNICODE));

              
               


          }



      }

      /**
       * 修改时间
       */
      public function times($time){

          return    $times=date("Y-m-d H:i:s",strtotime("+2 day",strtotime("$time")));

      }





    /**
     * 返回任务标题
     * @param  string $art_id [description]
     * @return [type]         [description]
     */
    public function title($art_id='null'){
                
              $objCriteria = new CDbCriteria;
              $objCriteria->select = '*';
              $objCriteria->addCondition("id='$art_id'"); 
              $objCriteria->addCondition("status='1'"); 
              $Article  = Article::model()->findAll($objCriteria);
              if (empty($Article )) {
                return $array='';
              }
              foreach ($Article as $key => $value) {
                $title=$value['title'];
              }

              return $title;
    }



    /**
     *
     *当前审核状态完善
     */
    public function status($status='null',$content='null',$type='null'){

        if ($status=='0'&&$type=='2') {
            return $status='待审核';
        }elseif($status=='2'&&$type=='2'){
            return $status='审核通过';
        }elseif ($status=='5'&&$type=='2') {
            return $status='已拍视频';
        }



        	if ($status=='0') {
        		return $status='上传';
        	}elseif ($status=='1') {
        		return $status='待审核';
        	}elseif ($status=='2') {
        		return $status='审核通过';
        	}elseif ($status=='3') {
    		// return $array=array(
    		// 	'status'=>$status,
    		// 	'content'=>$content
    		// 	);
                   if($type=='1') {
                       return $status='驳回原因';
                   }else if ($type=='2') {
                       return $status='审核失败';
                   } 
        	}elseif($status=='4'){
        		return $status='已逾期';
        	}



    }


    /**
     *
     *当前审核状态颜色值返回
     */
    public function collor($status='null',$content='null',$type='null'){

        if ($status=='0'&&$type=='2') {
              return $status='#1f75cc';
        }elseif($status=='2'&&$type=='2'){

              return $status='#0fd60b';
        }elseif ($status=='5'&&$type=='2') {
            return $status='#a7a7a7';
        }


          if ($status=='0') {
            return $status='#ed6f01';
          }elseif ($status=='1') {
            return $status='#1f75cc';
          }elseif ($status=='2') {
            return $status='#0fd60b';
          }elseif ($status=='3') {
               if($type=='1') {
                   return $status='#ed6f01';
               }else if ($type=='2'){
                   return $status='#a7a7a7';
               } 
          }elseif($status=='4'){
            return $status='#a7a7a7';
          }



    }


    	/**
		 *
		 * 个人中心 --完善资料查看
		 * {"openid":"okIRe0uwervqEsWtnZAcb6OivbD0"}
		 */
		public function actionUser(){

          $post=file_get_contents('php://input');
          $data=json_decode($post,true);
          
        if (empty($data)) { 
          		$output = array('data'=>NULL, 'info'=>'你没有数据', 'code'=>100);
             	exit(json_encode($output,JSON_UNESCAPED_UNICODE));
          }else{

     		
          	  $openid   = isset($data['openid'])?$data['openid']:'';
      
          	  //获取用户的信息
              $objCriteria = new CDbCriteria;
              $objCriteria->select = '*';
              $objCriteria->addCondition("openid='$openid'"); 
              $userObj  = WechatUser::model()->findAll($objCriteria);

              if (empty($userObj)) {
                  $output = array('data'=>NULL, 'info'=>'没有当前用户', 'code'=>101);
                  exit(json_encode($output,JSON_UNESCAPED_UNICODE));             
              }
              foreach ($userObj as $key => $value){
            			$servicetype=$value['occ_id'];

              }
         
          
              switch ($servicetype) {
                		case '1':
                			$user=$this->Doctor($openid);
                      $user['occ_id']='1';
                   
                			$output = array('data'=>$user, 'info'=>'成功', 'code'=>200);
             				   exit(json_encode($output,JSON_UNESCAPED_UNICODE));
                			break;

                		 case '2':
                    
                			 $user=$this->Dietitian($openid);
                       $user['occ_id']='2';
                			$output = array('data'=>$user, 'info'=>'成功', 'code'=>200);
             				exit(json_encode($output,JSON_UNESCAPED_UNICODE));
                			break;

                      case '3':

                      $output = array('info'=>'您没有权限', 'code'=>205);
                    exit(json_encode($output,JSON_UNESCAPED_UNICODE));
                      break;

                    case '0':
                      $output = array('info'=>'您需要注册', 'code'=>208);
                    exit(json_encode($output,JSON_UNESCAPED_UNICODE));
                      break;

             }  	


		}
	}



      /**
       * 医生权限判断
       */
      public function Doctor($openid=NULL){
    
			       $objCriteria = new CDbCriteria;
              $objCriteria->select = '*';
              $objCriteria->addCondition("openid='$openid'"); 
              $Doctor     = Doctor::model()->findAll($objCriteria);
	
		  	  if(empty($Doctor)){
		          		$output = array('info'=>'请完善您的信息(医生)', 'code'=>106);
		             	exit(json_encode($output,JSON_UNESCAPED_UNICODE));
		  	  }
		  	 
  			
  				$user=new Doctor();

  			return 	$doctor=$user->message($Doctor);


      }


      /**
       *营养师权限判断
       */
      public function Dietitian($openid=NULL){


			        $objCriteria = new CDbCriteria;
              $objCriteria->select = '*';
              $objCriteria->addCondition("openid='$openid'"); 
              $Nutrition  = Nutrition::model()->findAll($objCriteria);
        	  		if(empty($Nutrition)){
                		$output = array('info'=>'请完善您的信息(营养师)', 'code'=>107);
                   	exit(json_encode($output,JSON_UNESCAPED_UNICODE));
        		    }
  				$user=new Nutrition();

  				return 	$Nutrition=$user->message($Nutrition);

      }




  	/**
     * 个人中心 -消费积分
     * {"id":"189"}
     */
    public function  actionMyIntegral(){
          $post=file_get_contents('php://input');
          $data=json_decode($post,true);
         
        if (empty($data)){ 
              $output = array('data'=>NULL, 'info'=>'你没有数据', 'code'=>101);
              exit(json_encode($output,JSON_UNESCAPED_UNICODE));
          }else{  
                $id   = isset($data['id'])?$data['id']:'';
              $user=new WechatIntegralDetails();
              $mySurplus=$user->myIntegral($id);
              $output = array('data'=>$mySurplus,'info'=>'成功', 'code'=>201);
                exit(json_encode($output,JSON_UNESCAPED_UNICODE));            
          }    

    }


    /**
     *个人中心 - 提现金记录表
     * {"id":"187"}
     */
    public function actionMymoney(){
        $post=file_get_contents('php://input');
          $data=json_decode($post,true);
        if (empty($data)){ 
              $output = array('data'=>NULL, 'info'=>'你没有数据', 'code'=>101);
              exit(json_encode($output,JSON_UNESCAPED_UNICODE));
          }else{  
                $id       =  isset($data['id'])?$data['id']:'';
                $user     =  new Withdrawals();
                $mySurplus=  $user->myMoney($id);
                $output   =  array('data'=>$mySurplus,'info'=>'成功', 'code'=>201);
                exit(json_encode($output,JSON_UNESCAPED_UNICODE));            
          }   

    }



    /**
     *个人中心 -提现金
     * {"id":"189","money":"20"}
     */
    public function actionMoney(){
    	
          $post=file_get_contents('php://input');
          $data=json_decode($post,true);
                          $time=date("Y-m-d H:i:s");
        
        if (empty($data)){ 
                $output = array('data'=>NULL, 'info'=>'你没有数据', 'code'=>101);
                exit(json_encode($output,JSON_UNESCAPED_UNICODE));
          }else{  

               $id   = isset($data['id'])?$data['id']:'';
               $money   = isset($data['money'])?$data['money']:'';
               $money=intval($money);
    
               if ($id==''||$money=='') {
                $output = array('data'=>NULL, 'info'=>'没有数据', 'code'=>100);
                exit(json_encode($output,JSON_UNESCAPED_UNICODE));              
               }

               if ($money>100) {
                $output = array('data'=>NULL, 'info'=>'最多上限可提取100', 'code'=>103);
                exit(json_encode($output,JSON_UNESCAPED_UNICODE));  
               }

                  //获取当前用户总积分
                  $wechat_user=new WechatUser();
                  $myuser=$wechat_user->userMoney($id);
                  $num=intval($myuser['money']);
                  //当前金钱-消费金钱=剩余金钱
                  $surplus=$num-$money;
                  if ($num<$money) {
                  $output = array('info'=>'你的余额不足', 'code'=>104);
                  exit(json_encode($output,JSON_UNESCAPED_UNICODE));                  
                  }
                  //查询当前数据中是否已经有提交记录
                    $objCriteria = new CDbCriteria;
                    $objCriteria->select = '*';
                    $objCriteria->addCondition("user_id='$id'"); 
                    $objCriteria->addCondition("status='1'"); 
                    $record=Withdrawals::model()->findAll($objCriteria);
                   if (!empty($record)) { 
                     $output = array('info'=>'您已经有提交记录,请管理员审核后在提交', 'code'=>102);
                     exit(json_encode($output,JSON_UNESCAPED_UNICODE));
                  }

               try{
                  $dbTrans= Yii::app()->db->beginTransaction();
                  $Withdrawals=new Withdrawals();
                  $Withdrawals->username=$myuser['username'];
                  $Withdrawals->cell_phone=$myuser['cell_phone'];
                  $Withdrawals->user_id=$myuser['user_id'];
                  $Withdrawals->money=$money; 
                  $Withdrawals->create_time=$time;
                  // //修改我的数据
                  // $myIntegral=new WechatUser();
                  // $count=$myIntegral->myMoney($id,$surplus);
                  // !empty($count)&&
            if ($Withdrawals->save()) {
                                $dbTrans->commit();
                                $output = array('info'=>'提现成功', 'code'=>200);
                                 exit(json_encode($output,JSON_UNESCAPED_UNICODE));   
                        }else{
                            //抛出异常
                            throw new Exception('异常错误'); 
                        }
              }catch(Exception $e) {
                  print $e->getMessage();   
                  exit();         
              } 
          }
    }


    /**
     *根据ID查看审核失败原因
     * {"id":"1"}
     */
    public function actionContent(){

		      $post=file_get_contents('php://input');
          $data=json_decode($post,true);
          
        if(empty($data)) { 
               $output = array('data'=>NULL, 'info'=>'你没有数据', 'code'=>101);
               exit(json_encode($output,JSON_UNESCAPED_UNICODE));
          }else{
               $id=isset($data['id'])?$data['id']:'';

               $objCriteria = new CDbCriteria;
               $objCriteria->select = 'id,content';
               $objCriteria->addCondition("id='$id'"); 
               $wechat_article_receive  =Articlereceive::model()->findAll($objCriteria);

              if(empty($wechat_article_receive)){
                     $output = array('info'=>'你没有审核失败消息', 'code'=>201);
                     exit(json_encode($output,JSON_UNESCAPED_UNICODE));
              }

              foreach ($wechat_article_receive as $key => $value) { 
                    $article_receive=array(
                    	   'id'      =>$value['id'],
                         'content' =>$value['content'],
                      );
              }

           	      $output = array('data'=>$article_receive, 'info'=>'成功', 'code'=>200);
                  exit(json_encode($output,JSON_UNESCAPED_UNICODE));

              
               


          }



    }



    /**
     * 完善信息修改营养师
     * 
     */
     public function actionDietitians(){
      set_time_limit(0);
        $path = $_SERVER['DOCUMENT_ROOT'];

        include($path."/protected/models/Nutrition.php");
        include($path."/protected/models/Themephoto.php");
         $user_id    = isset($_POST['user_id'])?$_POST['user_id']:'';
         
         if ($user_id=='') {
                    $output = array('info'=>'数据为空', 'code'=>101);
                    exit(json_encode($output,JSON_UNESCAPED_UNICODE));      
         }

              $objCriteria = new CDbCriteria;
              $objCriteria->select = 'id,text';
              $objCriteria->addCondition("id='$user_id'"); 
              $wechat_user=WechatUser::model()->findAll($objCriteria);

              foreach ($wechat_user as $key => $value) {
                    $array=array(
                      'text'=>$value['text'],
                      );
              }

              if (empty($array)){
                    $output = array('info'=>'没有修改值', 'code'=>199);
                    exit(json_encode($output,JSON_UNESCAPED_UNICODE));                
              }

              $prompt = new Prompt();
              $accessKey = $prompt->ACCESSKEY;
              $secretKey = $prompt->SEECRETKEY;
              $auth = new Auth($accessKey, $secretKey);
              $bucket = $prompt->BUCKET;  
              $uploadMgr = new UploadManager(); 
              $token =  $auth->uploadToken($bucket,NULL, 3600);
              //定义一个字符串
              $number=rand(1000,9999);
              $time=date('Y-m-d H:i:s');
              $str='';

      
              $all=rtrim($array['text'], ",");
              $hello = explode(',',$all); 
   
              
                for ($i=0; $i <count($hello) ; $i++) { 
  
                       if ($hello[$i]=='相关职称') {
                              $doctortitle    = isset($_POST['doctortitle'])?$_POST['doctortitle']:'';
                            
                                  $str.="doctortitle='$doctortitle',";        
                            }



                          if ($hello[$i]=='个人照片') {

                          if (empty($_FILES['personal_img']['tmp_name'])) {
                            $output = array('info'=>'个人照片不可为空', 'code'=>301);
                          exit(json_encode($output,JSON_UNESCAPED_UNICODE));                       
                          }
                                    $personal_img = $_FILES['personal_img']['tmp_name'];
                                    $personal_imgs = $_FILES['personal_img']['name'];
                                    $arraypersonal_img = explode('.',$personal_imgs);
                                    $personal_img_name= time().$number.'personal_img'.'.'.$arraypersonal_img[1];  
                                    $personal_img_address=list($ret, $err) = $uploadMgr->putFile($token, $personal_img_name, $personal_img); 

                                    $personal_img_name='http://ttweb.yingyangshi.com/'.$personal_img_name.'?imageMogr2/thumbnail/750x388!';
                                $str.='personal_img='."'$personal_img_name',";



                          }

                          if ($hello[$i]=='资格证') {

                          if (empty($_FILES['qualification_img']['tmp_name'])) {
                            $output = array('info'=>'资格证不可为空', 'code'=>302);
                          exit(json_encode($output,JSON_UNESCAPED_UNICODE));                       
                          }
                            $qualification_img = $_FILES['qualification_img']['tmp_name'];
                            $qualification_imgs = $_FILES['qualification_img']['name'];
                            $arrayqualification_img = explode('.',$qualification_imgs);
                            $qualification_img_name= time().$number.'qualification_img'.'.'.$arrayqualification_img[1];  
                            $qualification_img_address=list($ret, $err) = $uploadMgr->putFile($token, $qualification_img_name, $qualification_img); 
                              $qualification_img_name='http://ttweb.yingyangshi.com/'.$qualification_img_name;

                              $str.='qualification_img='."'$qualification_img_name',";

                          }
                }

            $str.='update_time='."'$time',";
            $up=rtrim($str, ",");

            //修改数据库信息
            $doctor=new Nutrition();
            $update=$doctor->uploadtwo($user_id,$up);

            if ($update) {
                          $output = array('info'=>'修改成功', 'code'=>200);
                          exit(json_encode($output,JSON_UNESCAPED_UNICODE));
            }    


     }



    /**
     * 完善信息修改医生
     */
     public function actionDoctors(){
      set_time_limit(0);
        $path = $_SERVER['DOCUMENT_ROOT'];
        include($path."/protected/models/Themephoto.php");

         $user_id = isset($_POST['user_id'])?$_POST['user_id']:'';

         if ($user_id=='') {
                    $output = array('info'=>'数据为空', 'code'=>101);
                    exit(json_encode($output,JSON_UNESCAPED_UNICODE));      
         }

              $objCriteria = new CDbCriteria;
              $objCriteria->select = 'id,text';
              $objCriteria->addCondition("id='$user_id'"); 
              $wechat_user=WechatUser::model()->findAll($objCriteria);

              foreach ($wechat_user as $key => $value) {
                    $array=array(
                      'text'=>$value['text'],
                      );
              }

              if (empty($array)) {
                    $output = array('info'=>'没有修改值', 'code'=>199);
                    exit(json_encode($output,JSON_UNESCAPED_UNICODE));                
              }

          $prompt = new Prompt();
          $accessKey = $prompt->ACCESSKEY;
          $secretKey = $prompt->SEECRETKEY;
          $auth = new Auth($accessKey, $secretKey);
          $bucket = $prompt->BUCKET;  
          $uploadMgr = new UploadManager(); 

          $token =  $auth->uploadToken($bucket,NULL, 3600);
          //定义一个字符串
          $str='';
          $number=rand(1000,9999);
          $all=rtrim($array['text'], ",");

          $hello = explode(',',$all); 
    
         
          for ($i=0; $i <count($hello); $i++) { 
                if ($hello[$i]=='医生职称'){
           
                  $doctortitle    = isset($_POST['doctortitle'])?$_POST['doctortitle']:'';

                  if ($doctortitle=='') {
                    $output = array('info'=>'没有医生职称数据', 'code'=>207);
                    exit(json_encode($output,JSON_UNESCAPED_UNICODE));
                  }
                      $str.='doctortitle='."'$doctortitle',";        
                }

              
                if ($hello[$i]=='所属科室') {
                  $department    = isset($_POST['department'])?$_POST['department']:'';
                   if ($department=='') {
                    $output = array('info'=>'没有所属科室数据', 'code'=>208);
                    exit(json_encode($output,JSON_UNESCAPED_UNICODE));
                  }
                      $str.='department='."'$department',";        
                }

                if ($hello[$i]=='现坐诊医院') {
                  $hospital= isset($_POST['hospital'])?$_POST['hospital']:'';
                  if ($hospital=='') {
                    $output = array('info'=>'没有现坐诊医院数据', 'code'=>209);
                    exit(json_encode($output,JSON_UNESCAPED_UNICODE));
                  }
                  $str.='hospital='."'$hospital',"; 
                }

              if ($hello[$i]=='医生照片') {
                    if (empty($_FILES['personal_img']['tmp_name'])) {
                            $output = array('info'=>'医生照片不可为空', 'code'=>302);
                          exit(json_encode($output,JSON_UNESCAPED_UNICODE));                       
                          }
                        $personal_img = $_FILES['personal_img']['tmp_name'];
                        $personal_imgs = $_FILES['personal_img']['name'];
                        $arraypersonal_img = explode('.',$personal_imgs);
                        $personal_img_name= time().$number.'personal_img'.'.'.$arraypersonal_img[1];  
                        $personal_img_address=list($ret, $err) = $uploadMgr->putFile($token, $personal_img_name, $personal_img); 

                        $personal_img_name='http://ttweb.yingyangshi.com/'.$personal_img_name.'?imageMogr2/thumbnail/750x388!';
                    $str.='personal_img='."'$personal_img_name',";



              }

              if ($hello[$i]=='医师执业证') {
                                    if (empty($_FILES['practice_img']['tmp_name'])) {
                            $output = array('info'=>'医师执业证不可为空', 'code'=>302);
                          exit(json_encode($output,JSON_UNESCAPED_UNICODE));                       
                          }
                        $practice_img = $_FILES['practice_img']['tmp_name'];//'./php-logo.png';
                        $practice_imgs = $_FILES['practice_img']['name'];
                        $arraypractice_img = explode('.',$practice_imgs);
                        $practice_img_name= time().$number.'practice_img'.'.'.$arraypractice_img[1];  
                        $practice_img_address=list($ret, $err) = $uploadMgr->putFile($token, $practice_img_name, $practice_img); 

                    $practice_img_name='http://ttweb.yingyangshi.com/'.$practice_img_name;
                    $str.='practice_img='."'$practice_img_name',";
              

              }

        }
        $time=date('Y-m-d H:i:s');
        $str.='update_time='."'$time',";
     
        $up=rtrim($str, ",");

        //修改数据库信息
        $doctor=new Doctor();
        $update=$doctor->uploadtwo($user_id,$up);

          if ($update){
                      $output = array('info'=>'修改成功', 'code'=>200);
                      exit(json_encode($output,JSON_UNESCAPED_UNICODE));
        }      

     }

}
?>
