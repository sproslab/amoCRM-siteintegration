<?php



  $user=array(

   'USER_LOGIN'=>'LOGIN',

   'USER_HASH'=>'HASH'

);

  

$subdomain='subdomain_here';//$myrow_amo['domen']; #��� ������� - ��������

$link='https://'.$subdomain.'.amocrm.ru/private/api/auth.php?type=json';

 

$curl=curl_init(); #��������� ���������� ������ cURL

#������������� ����������� ����� ��� ������ cURL

curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');

curl_setopt($curl,CURLOPT_URL,$link);

curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');

curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($user));

curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));

curl_setopt($curl,CURLOPT_HEADER,false);

curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__

curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__

curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);

curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);

 

$out=curl_exec($curl); #���������� ������ � API � ��������� ����� � ����������

$code=curl_getinfo($curl,CURLINFO_HTTP_CODE); #������� HTTP-��� ������ �������

curl_close($curl); #��������� ����� cURL

$code=(int)$code;

$errors=array(

    301=>'Moved permanently',

    400=>'Bad request',

    401=>'Unauthorized',

    403=>'Forbidden',

    404=>'Not found',

    500=>'Internal server error',

    502=>'Bad gateway',

    503=>'Service unavailable'

);

try

{

    #���� ��� ������ �� ����� 200 ��� 204 - ���������� ��������� �� ������

   if($code!=200 && $code!=204)

        throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);

}

catch(Exception $E)

{

    die('������: '.$E->getMessage().PHP_EOL.'��� ������: '.$E->getCode());

}

?>