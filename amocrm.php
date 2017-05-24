<?php
$test = 'test';
	$db = mysql_connect ("localhost","LOGIN","PASSWORD");

	mysql_select_db ("DB",$db);

	mysql_query('SET NAMES utf8');

	

	mb_internal_encoding('UTF-8');

	

	$minTasksUserId = mysql_fetch_array(

		mysql_query("

			SELECT

				id, manager_id

			FROM

				manager

			WHERE

				active='1'

			ORDER BY

				last_task ASC

			LIMIT

				1"

		,$db)

	);

	

	//die(var_dump($minTasksUserId['manager_id']));

	

	$tasksUserId = intval($minTasksUserId['id']);

	$managerId   = intval($minTasksUserId['manager_id']);

	

	$currTime = time();

	

	mysql_query ("UPDATE manager SET last_task = $currTime WHERE id = $tasksUserId", $db);



	 

	require_once "auth.php";

	require_once "function_amocrm.php";  



	$name =  $_POST['name'];

	$phone = $_POST['phone']; 

	$email = $_POST['email'];

	$name_lead = 'Забронировать | anthony-robbins-upw.ru';

	$type_lead = 'lead';

	  

	$num_ticket  = $_POST['quantity'];

	//$type_ticket = $_POST['ticketType'];



	$source = 1379036;

	$type_ticket = 1329464;

	$status_id = 14942089; //UPW 12775572

	$url = 'http://anthony-robbins-upw.ru/event/pw/';                      

	$sb_first_src = '';     

	$sb_first_mdm = '';   

	$sb_first_cmp = '';

	$sb_current_src = $_POST['utm_source'];

	$sb_current_mdm = $_POST['utm_medium'];

	$sb_current_cmp = $_POST['utm_campaign'];

	$sb_current_cnt = $_POST['utm_content'];

	$sb_current_trm = $_POST['utm_term'];



	$sb_current_add_rf = '';

	$sb_udata_vst      = '';



	$url = strval($url);

	$url = str_replace(' ', '', $url);

	$url = trim($url);

	$new_task = "false";



	$phone = strval($phone);

	$phone = str_replace(' ', '', $phone);

	$phone = trim($phone);



	$email = strval($email);

	$email = str_replace(' ', '', $email);

	$email = trim($email);



	$name = strval($name);

	$name = str_replace(' ', '', $name);

	$name = trim($name);

	 

	if( $curl = curl_init() ) {

		curl_setopt($curl, CURLOPT_URL, 'http://anthony-robbins-upw.ru/global_amocrm/getresponse.php');  

		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);

		curl_setopt($curl, CURLOPT_POST, true);

		curl_setopt($curl, CURLOPT_POSTFIELDS, "name=".$name."&email=".$email."&url=".$url);

		$out = curl_exec($curl);    

		curl_close($curl);

	} 

	 

	if($type_lead == "lead") {	

		$subject = "Новая заявка ".$name_lead; 

		$sendto   = "client@meetpartners.ru";

		$headers  = "From: Тони Роббинс \r\n";

		$headers .= "Reply-To: Тони Роббинс \r\n";

		$headers .= "MIME-Version: 1.0\r\n";

		$headers .= "Content-Type: text/html;charset=utf-8 \r\n";

		$msg  = "<html><body style='font-family:Arial,sans-serif;'>";

		$msg .= "<h2 style='font-weight:bold;border-bottom:1px dotted #ccc;'>".$name_lead."</h2>\r\n";

		$msg .= "<p><strong>От кого:</strong> ".$name."</p>\r\n";

		$msg .= "<p><strong>Телефон:</strong> ".$phone."</p>\r\n";

		$msg .= "<p><strong>Email:</strong> ".$email."</p>\r\n";

		$msg .= "</body></html>";

		@mail($sendto, $subject, $msg, $headers);

	}	



	$link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/contacts/list?query='.$phone;

	$array = curl_contact($link); // ищем контакт по телефону



	if(isset($array) and $phone !=="незадано") {

		$contact_id= $array['response']['contacts'][0]['id']; // id контакта 

		$linked_leads_id = $array['response']['contacts'][0]['linked_leads_id']; //массив сделок контакта 

	}else{

		$link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/contacts/list?query='.$email;

		$array = curl_contact($link); // ищем контакт по email

		

		if(isset($array)) {

			$contact_id= $array['response']['contacts'][0]['id']; // id контакта 

			$linked_leads_id = $array['response']['contacts'][0]['linked_leads_id']; //массив сделок контакта 

		}

	}

				 

	foreach($linked_leads_id as $leads_id){		//Если сделка на данный продукт существует то создаем повторную задачу  

		$link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/leads/list?id='.$leads_id;

		$array = curl_contact($link);

		$responsible_user_id_new_task = $array['response']['leads'][0]['responsible_user_id'];

		//die(var_dump());

		$new_task_status_id = $array['response']['leads'][0]['status_id'];

			  

		for($i=0; count($array['response']['leads'][0]['custom_fields'])>$i; $i++){

			if($array['response']['leads'][0]['custom_fields'][$i]['name'] == 'url') {

				if($array['response']['leads'][0]['custom_fields'][$i]['values'][0]['value'] == $url) {

					$new_task = "true";

					$leads_id_new_task = $leads_id;

				}

			}

		}

	}	  



	

	//????????????????????????????????????????????????????WTF?????????????????????????????\

	if($new_task == "true" and $new_task_status_id !== '143'){

	//die(var_dump($new_task));

		$tasks['request']['tasks']['add']=array(

		

			array(

				'element_id'=>$leads_id_new_task, #ID сделки

				'element_type'=>2, #Показываем, что это - сделка, а не контакт

				'task_type'=>1, #Звонок

				'text'=>$task,

				'responsible_user_id'=>$managerId,

				'complete_till'=>time()+600

			) 

		);

			 

		$link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/tasks/set';

		$array = curl_send($link, $tasks); //встраиваем задачу в новую сделку

		exit();

	}

		

	   

	$leads['request']['leads']['add']=array(

	

		array(

			'name'=>$name_lead,

			'status_id'=>$status_id,

			'price'=>0,

			'responsible_user_id'=>$managerId,

			'custom_fields'=>array(
				       array(
			        'id'=>589342,
			        'values'=>array(
			          array(
			            'value'=>$_POST['utm_source']
			          )
			        )
			      ),
			       array(
			        'id'=>589344,
			        'values'=>array(
			          array(
			            'value'=>$_POST['utm_medium']
			          )
			        )
			      ),
			       array(
			        'id'=>589346,
			        'values'=>array(
			          array(
			            'value'=>$_POST['utm_campaign']
			          )
			        )
			      ),
			       array(
			        'id'=>589350,
			        'values'=>array(
			          array(
			            'value'=>$_POST['utm_term']
			          )
			        )
			      ),
			       array(
			        'id'=>589348,
			        'values'=>array(
			          array(
			            'value'=>$_POST['utm_content']
			          )
			        )
			      ),
				array(

					'id'=>563566,

					'values'=>array(

						array(

							'value'=>$num_ticket  

						)

					)

				),

				array(

					'id'=>563552,

					'values'=>array(

						array(

							'value'=>$type_ticket  

						)

					)

				),

				array(

					'id'=>589334,

					'values'=>array(

						array(

							'value'=>$url 

						)

					)

				),

				array(

					'id'=>563558,

					'values'=>array(

						array(

							'value'=>$source // источник сайт

						)

					)

				),				

				array(

					'id'=>589336,

					'values'=>array(

						array(

							'value'=>$sb_first_src // first_source

						)

					)

				),

				array(

					'id'=>589338,

					'values'=>array(

						array(

							'value'=>$sb_first_mdm // first_medium

						)

					)

				),	

				array(

					'id'=>589340,

					'values'=>array(

						array(

							'value'=>$sb_first_cmp // first_campaign

						)

					)

				),

				array(

					'id'=>589342,

					'values'=>array(

						array(

							'value'=>$sb_current_src // current_source

						)

					)

				),	

				array(

					'id'=>589344,

					'values'=>array(

						array(

							'value'=>$sb_current_mdm // current_medium						

						)

					)

				),

				array(

					'id'=>589346,

					'values'=>array(

						array(

							'value'=>$sb_current_cmp // current_campaign

						)

					)

				),

				array(

					'id'=>589348,

					'values'=>array(

						array(

							'value'=>$sb_current_cnt  

						)

					)

				),

				array(

					'id'=>589350,

					'values'=>array(

						array(

							'value'=>$sb_current_trm  

						)

					)

				) 

			)

		)

	); 

	

	$link = 'https://'.$subdomain.'.amocrm.ru/private/api/v2/json/leads/set';

	$array = curl_send($link, $leads); // добавляем новую сделку

	 

	$lead_id = $array['response']['leads']['add'][0]['id']; //id новой сделки 

		if($source == 1379032 || $source == 1378962 || $source == 1379036) {


			$tasks['request']['tasks']['add']=array(

			#Привязываем к сделке

				array(

					'element_id'=>$lead_id, #ID сделки

					'element_type'=>2,      #Показываем, что это - сделка, а не контакт

					'task_type'=>1,         #Звонок

					'text'=>$task,

					'responsible_user_id'=>$managerId,

					'complete_till'=>time()+600

				) 

			);

			$link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/tasks/set';

			$array = curl_send($link, $tasks); //встраиваем задачу в новую сделку

		}

		 

		if(isset($contact_id)) { // если контакт существует прикрепляем его к сделки

			$linked_leads_id[] = $lead_id;

			$contacts['request']['contacts']['update']=array(

			array(

				'id'=>$contact_id,

				'last_modified'=> time(),

				'linked_leads_id'=>$linked_leads_id  

			)

		);

	

	}else{

	

		$linked_leads_id = array($lead_id);

		$contacts['request']['contacts']['add']=array(

	

		array(

			'name'=>$name, #Имя контакта

			'last_modified'=>time(), //optional

			'linked_leads_id'=>$linked_leads_id,

			'responsible_user_id'=>$managerId,

			'custom_fields'=>array(

				array(

					'id'=>530274,

					'values'=>array(

					array(

						'value'=>$phone,

						'enum'=>'OTHER'

					)

					)

				),

				array(

					'id'=>530276,

					'values'=>array(

						array(

							'value'=>$email,

							'enum'=>'OTHER'

							)

						)

					)

				)

			)

		);

	 

	}



	$link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/contacts/set';

	$array = curl_send($link, $contacts);

	 

	//var_dump('Succes10');

?>