<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Utility {

	private static $db;

	private  $ci;

	public static $countryArray = array();

	public static $customerCountryArray = array();

	public static $templateArray = array();

	public static $currencyArray = array();

	public static $unitArray = array();

	public static $stateArray = array();

	public static $cityArray = array();

	public static $staffCityArray = array();

	public static $typeArray = array();

	public static $balanceTypeArray = array();

	public static $allowedcurrency = array();

	public static $yesnoArray = array();

	public static $invoicetypeArray = array();

	public static $pamentmodeArray = array();

	public static $imageTypeArray = array();


	public static $designationArray = array();




	public static $mailtemplate_table = 'mailtemplate';



	public static $photo_table = 'photo';

	public static $statusArray = array();



	public static $genderArray = array();

	public static $genderAPIArray = array();



	public static $accessArray = array();



	public static $daysArray = array();

	public static $daysAPIArray = array();

	public static $timeArray = array();

	public static $timeAPIArray = array();

	public static $medicalHistoryArray = array();

	public static $batchStatusAPIArray = array();

	public static $isParentArray = array();

	public static $childCustodyArray = array();

	public static $bloodGroupArray = array();

	public static $siblingArray = array();

	public static $staffPaymentArray = array();

	function __construct()

	{

		$this->ci =& get_instance();

		$this->ci->load->database();

		self::$db = &get_instance()->db;

		self::$countryArray = array('UAE');

		self::$stateArray = array('Abu dhabi','Dubai', 'Sharjah', 'Ajman','Umm AL-Quwain', 'Rsa- Al-Khaimah','Fujairah');

		self::$cityArray = array('Ajman','Aj Ain', 'Masdar City','Fujairah','Khor Fakkan');

		self::$staffCityArray = array('','Ajman','Aj Ain', 'Masdar City','Fujairah','Khor Fakkan');

		self::$typeArray = array('UAE','Export', 'Export Sale (without document)', 'Out of VAT Scope');

		self::$balanceTypeArray =  array('Unpaid','Paid');

		self::$pamentmodeArray = array('Cash'=>'Cash','Bank'=>'Bank');

		self::$imageTypeArray = array('.png','.bmp','.jpg','.jpeg');

		self::$yesnoArray = array('0'=>'No','1'=>'Yes');

		self::$customerCountryArray = array('Afghanistan'=>'Afghanistan','Albania'=>'Albania','Andorra'=>'Andorra','Angola'=>'Angola','Antigua and Barbuda'=>'Antigua and Barbuda','Argentina'=>'Argentina','Armenia'=>'Armenia','Australia'=>'Australia','Austria'=>'Austria','Azerbaijan'=>'Azerbaijan','Bahamas'=>'Bahamas','Bahrain'=>'Bahrain','Bangladesh'=>'Bangladesh','Barbados'=>'Barbados','Belarus'=>'Belarus','Begium'=>'Begium','Belize'=>'Belize','Benin'=>'Benin','Bhutan'=>'Bhutan','Bolivia'=>'Bolivia','Bosnia and Herzegovina'=>'Bosnia and Herzegovina','Botswana'=>'Botswana','Brazil'=>'Brazil','Brunei'=>'Brunei','Bulgaria'=>'Bulgaria','Burkina Faso'=>'Burkina Faso','Burundi'=>'Burundi','Cambodia'=>'Cambodia','Cameroon'=>'Cameroon','Canada'=>'Canada','Cape Verde'=>'Cape Verde','Cayman Islands'=>'Cayman Islands','Central African Republic'=>'Central African Republic','Chad'=>'Chad','Chile'=>'Chile','China'=>'China','Colombia'=>'Colombia','Comoros'=>'Comoros','Costa Rica'=>'Costa Rica','Cote d’Ivoire'=>'Cote d’Ivoire','Croatia'=>'Croatia','Cuba'=>'Cuba','Cyprus'=>'Cyprus','CzechRepublic'=>'CzechRepublic','Democratic Republic of the Congo'=>'Democratic Republic of the Congo','Denmark'=>'Denmark','Djibouti'=>'Djibouti','Dominica'=>'Dominica','Dominican Republic'=>'Dominican Republic','East Timor'=>'East Timor','Ecuador'=>'Ecuador','Egypt'=>'Egypt','El Salvador'=>'El Salvador','EquatorialGuinea'=>'EquatorialGuinea','Eritrea'=>'Eritrea','Estonia'=>'Estonia','Ethiopia'=>'Ethiopia','Fiji'=>'Fiji','Finland'=>'Finland','France'=>'France','French Guiana'=>'French Guiana','Gabon'=>'Gabon','Georgia'=>'Georgia','Germany'=>'Germany','Ghana'=>'Ghana','Greece'=>'Greece','Grenada'=>'Grenada','Guatemala'=>'Guatemala','Guinea'=>'Guinea','Guinea-Bissau'=>'Guinea-Bissau','Guyana'=>'Guyana','Haiti'=>'Haiti','Hondura'=>'Hondura','Hungary'=>'Hungary','Iceland'=>'Iceland','India'=>'India','Indonesia'=>'Indonesia','Iran'=>'Iran','Iraq'=>'Iraq','Israel'=>'Israel','Italy'=>'Italy','Jamaica'=>'Jamaica','Japan'=>'Japan','Jordan'=>'Jordan','Kazakhstan'=>'Kazakhstan','Kenya'=>'Kenya','Kiribati'=>'Kiribati','Kuwait'=>'Kuwait','Kyrgyzstan'=>'Kyrgyzstan','Laos'=>'Laos','Latvia'=>'Latvia','Lebanon'=>'Lebanon','Lesotho'=>'Lesotho','Liberia'=>'Liberia','Libya'=>'Libya','Liechtenstein'=>'Liechtenstein','Lithuania'=>'Lithuania','Luxembourg'=>'Luxembourg','Madagascar'=>'Madagascar','Malawi'=>'Malawi','Malaysia'=>'Malaysia','Maldives'=>'Maldives','Mali'=>'Mali','Malta'=>'Malta','Marshall Islands'=>'Marshall Islands','Mauritania'=>'Mauritania','Mauritius'=>'Mauritius','Mexico'=>'Mexico','Micronesia'=>'Micronesia','Moldova'=>'Moldova','Monaco'=>'Monaco','Mongolia'=>'Mongolia','Montenegro'=>'Montenegro','Morocco'=>'Morocco','Mozambique'=>'Mozambique','Myanmar'=>'Myanmar','Namibia'=>'Namibia','Nauru'=>'Nauru','Nepal'=>'Nepal','Netherlands'=>'Netherlands','New Zealand'=>'New Zealand','Nicaragua'=>'Nicaragua','Niger'=>'Niger','Nigeria'=>'Nigeria','North Korea'=>'North Korea','Norway'=>'Norway','Oman'=>'Oman','Pakistan'=>'Pakistan','Palau'=>'Palau','Palestine'=>'Palestine','Panama'=>'Panama','Papua New Guinea'=>'Papua New Guinea','Paraguay'=>'Paraguay','Peru'=>'Peru','Philippines'=>'Philippines','Poland'=>'Poland','Portugal'=>'Portugal','Puerto Rico'=>'Puerto Rico','Qatar'=>'Qatar','Republic of Ireland'=>'Republic of Ireland','Republic ofMacedonia'=>'Republic ofMacedonia','Republic of the Congo'=>'Republic of the Congo','Romania'=>'Romania','Russia'=>'Russia','Russia'=>'Russia','Rwanda'=>'Rwanda','Saint Kitts and Nevis'=>'Saint Kitts and Nevis','Saint Lucia'=>'Saint Lucia','Saint Vincent and the Grenadines'=>'Saint Vincent and the Grenadines','Samoa'=>'Samoa','San Marino'=>'San Marino','Sao Tome and Principe'=>'Sao Tome and Principe','Saudi Arabia'=>'Saudi Arabia','Senegal'=>'Senegal','Serbia'=>'Serbia','Seychelles'=>'Seychelles','Sierra Leone'=>'Sierra Leone','Singapore'=>'Singapore','Slovakia'=>'Slovakia','Slovenia'=>'Slovenia','Solomon Islands'=>'Solomon Islands','Somalia'=>'Somalia','South Africa'=>'South Africa','South Korea'=>'South Korea','South Sudan'=>'South Sudan','Spain'=>'Spain','Sri Lanka'=>'Sri Lanka','Sudan'=>'Sudan','Suriname'=>'Suriname','Swaziland'=>'Swaziland','Sweden'=>'Sweden','Switzerland'=>'Switzerland','Syria'=>'Syria','Taiwan'=>'Taiwan','Taiwan'=>'Taiwan','Tajikistan'=>'Tajikistan','Tanzania'=>'Tanzania','Thailand'=>'Thailand','The Gambia'=>'The Gambia','Togo'=>'Togo','Tonga'=>'Tonga','Trinidad and Tobago'=>'Trinidad and Tobago','Tunisia'=>'Tunisia','Turkey'=>'Turkey','Turkey'=>'Turkey','Turkmenistan'=>'Turkmenistan','Turks and Caicos'=>'Turks and Caicos','Tuvalu'=>'Tuvalu','Uganda'=>'Uganda','Ukraine'=>'Ukraine','UAE'=>'UAE','United Kingdom'=>'United Kingdom','United States'=>'United States','Uruguay'=>'Uruguay','Uzbekistan'=>'Uzbekistan','Vanuatu'=>'Vanuatu','Vatican City'=>'Vatican City','Venezuela'=>'Venezuela','Vietnam'=>'Vietnam','Western Sahara'=>'Western Sahara','Yemen'=>'Yemen','Zambia'=>'Zambia','Zimbabwe'=>'Zimbabwe');

		self::$templateArray = array('template1'=>'Template1', 'template2'=>'Template2');

		self::$currencyArray = array('AED'=>'AED','AFN'=>'AFN','ALL'=>'ALL','AMD'=>'AMD','ANG'=>'ANG','AOA'=>'AOA','ARS'=>'ARS','AUD'=>'AUD','AWG'=>'AWG','AZN'=>'AZN','BAM'=>'BAM','BBD'=>'BBD','BDT'=>'BDT','BGN'=>'BGN','BHD'=>'BHD','BIF'=>'BIF','BMD'=>'BMD','BND'=>'BND','BOB'=>'BOB','BRL'=>'BRL','BSD'=>'BSD','BTN'=>'BTN','BWP'=>'BWP','BYN'=>'BYN','BZD'=>'BZD','CAD'=>'CAD','CDF'=>'CDF','CHF'=>'CHF','CLP'=>'CLP','CNY'=>'CNY','COP'=>'COP','CRC'=>'CRC','CUP'=>'CUP','CVE'=>'CVE','CZK'=>'CZK','DJF'=>'DJF','DKK'=>'DKK','DOP'=>'DOP','DZD'=>'DZD','EGP'=>'EGP','ERN'=>'ERN','ETB'=>'ETB','EUR'=>'EUR','FJD'=>'FJD','FKP'=>'FKP','GBP'=>'GBP','GEL'=>'GEL','GGP'=>'GGP','GHS'=>'GHS','GIP'=>'GIP','GMD'=>'GMD','GNF'=>'GNF','GTQ'=>'GTQ','GYD'=>'GYD','HKD'=>'HKD','HNL'=>'HNL','HRK'=>'HRK','HTG'=>'HTG','HUF'=>'HUF','IDR'=>'IDR','ILS'=>'ILS','IMP'=>'IMP','INR'=>'INR','IQD'=>'IQD','IRR'=>'IRR','ISK'=>'ISK','JEP'=>'JEP','JMD'=>'JMD','JOD'=>'JOD','JPY'=>'JPY','KES'=>'KES','KGS'=>'KGS','KHR'=>'KHR','KMF'=>'KMF','KPW'=>'KPW','KRW'=>'KRW','KWD'=>'KWD','KYD'=>'KYD','KZT'=>'KZT','LAK'=>'LAK','LBP'=>'LBP','LKR'=>'LKR','LRD'=>'LRD','LSL'=>'LSL','LYD'=>'LYD','MAD'=>'MAD','MDL'=>'MDL','MGA'=>'MGA','MKD'=>'MKD','MMK'=>'MMK','MNT'=>'MNT','MOP'=>'MOP','MRO'=>'MRO','MUR'=>'MUR','MVR'=>'MVR','MWK'=>'MWK','MXN'=>'MXN','MYR'=>'MYR','MZN'=>'MZN','NAD'=>'NAD','NGN'=>'NGN','NIO'=>'NIO','NOK'=>'NOK','NPR'=>'NPR','NZD'=>'NZD','OMR'=>'OMR','PEN'=>'PEN','PGK'=>'PGK','PHP'=>'PHP','PKR'=>'PKR','PLN'=>'PLN','PYG'=>'PYG','QAR'=>'QAR','RON'=>'RON','RSD'=>'RSD','RUB'=>'RUB','RWF'=>'RWF','SAR'=>'SAR','SBD'=>'SBD','SCR'=>'SCR','SDG'=>'SDG','SEK'=>'SEK','SGD'=>'SGD','SHP'=>'SHP','SLL'=>'SLL','SOS'=>'SOS','SRD'=>'SRD','SSP'=>'SSP','STD'=>'STD','SYP'=>'SYP','SZL'=>'SZL','THB'=>'THB','TJS'=>'TJS','TMT'=>'TMT','TND'=>'TND','TOP'=>'TOP','TRY'=>'TRY','TTD'=>'TTD','TWD'=>'TWD','TZS'=>'TZS','UAH'=>'UAH','UGX'=>'UGX','USD'=>'USD','UYU'=>'UYU','UZS'=>'UZS','VEF'=>'VEF','VND'=>'VND','VUV'=>'VUV','WST'=>'WST','XAF'=>'XAF','XCD'=>'XCD','XDR'=>'XDR','XOF'=>'XOF','XPF'=>'XPF','YER'=>'YER','ZAR'=>'ZAR','ZMW'=>'ZMW');

		self::$unitArray = array('Per Piece'=>'Per Piece','Meter'=>'Meter','Cm'=>'Cm','Kg'=>'Kg','gm'=>'gm');

		self::$invoicetypeArray =  array(array('id'=>'UAE', 'value'=>'UAE'),array('id'=>'Export', 'value'=>'Export'));

		self::$allowedcurrency =  array(array('id'=>'AED', 'value'=>'AED'),array('id'=>'USD', 'value'=>'USD'));

		self::$designationArray = array('CA','Accountant', 'Accounting Clerk', 'Auditor','Chief Financial Officer', 'Controller','Financial Analyst');

		self::$daysAPIArray = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday', 'Sunday');

		self::$daysArray = array('Monday'=>'Monday','Tuesday'=>'Tuesday','Wednesday'=>'Wednesday','Thursday'=>'Thursday','Friday'=>'Friday','Saturday'=>'Saturday', 'Sunday'=>'Sunday');

		self::$timeArray = $this->ci->config->item('custom_time');

		self::$timeAPIArray = $this->ci->config->item('api_custom_time');

		self::$statusArray = array('active'=>'Active','inactive'=>'Inactive');

		self::$genderArray = array('Male'=>'Male','Female'=>'Female');

		self::$genderAPIArray = array('Male','Female');

		self::$accessArray = array(''=>'Select','all'=>'All','class'=>'Class');

		self::$bloodGroupArray = array(

										'o+'=>'O+',

										'o-'=>'O-',

										'a+'=>'A+',

										'a-'=>'A-',

										'b+'=>'B+',

										'b-'=>'B-',

										'ab+'=>'AB+',

										'ab-'=>'AB-',

										);

	}

	/**

	 * @return Random unique username

	 */


	static function callSendMail($to,$massage,$subject){
        $ci = & get_instance();
        $ch = curl_init();
        $url="http://k2key.in/ring/index.php/Mail/sendMail";
		$bccArray = "";
        $fields = array(
            'to' => $to,
            'subject' => $subject,
            'template' =>$massage,
            'bccArray' =>$bccArray

        );
		// print_r($fields);exit;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            echo "cURL error ({$errno}):\n {$error_message}";
        }
        curl_close($ch);
    }

	static function sendMailFromUtility($to,$massage,$subject){ 
	   // echo 'sendMailFromUtility'; exit;
		$ci = & get_instance();
		$bccArray = array();
		$config = Array(
    		'protocol' => 'smtp',
            'smtp_host' => 'smtp-relay.sendinblue.com ',
    		'smtp_port' => 587,
            'smtp_user' => 'ring.sancy2022@gmail.com',
            'smtp_pass' => 'XGpV3NRM5jta0KhL',
    		'mailtype'  => 'html',
    		'charset'   => 'iso-8859-1',
    		'smtp_crypto' =>  'SSL',
    		'smtp_timeout' => 30
		);

		$ci->load->library('email', $config);
        $ci->email
		->from("donotreplysancy@gmail.com", "Ring")
		// ->to($data->to)
		->to("mishraravi520@gmail.com")
		->subject($subject)
		->message($massage)
		->set_mailtype('html');

 	    $ci->email->bcc($bccArray);
		$ci->email->send();
        echo "last line";
        echo $ci->email->print_debugger(); exit;
	}

	static function callSendMailwithAttachedFile($to,$massage,$subject,$file){
        $ci = & get_instance();
        $ch = curl_init();
        $url="https://k2key.in/ring/index.php/Mail/sendMailwithAttachedFile";
		$bccArray = "";
        $fields = array(
            'to' => $to,
            'subject' => $subject,
            'template' =>$massage,
            'bccArray' =>$bccArray,
			'attachfile' =>$file

        );
		// print_r($fields);exit;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            echo "cURL error ({$errno}):\n {$error_message}";
        }
        curl_close($ch);
    }
	static function callSendMailNew($to,$emailMessage,$subject){
		$url = "https://api.sendinblue.com/v3/smtp/email";

    	$headers = array('api-key: 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',
    		'Content-Type: application/json');

    // $custJsonData['attachment']    = $attachment_list;
		// $cc	= 'pankaj.sevlani@gmail.com';
		// $bcc	= 'pankaj.sevlani@gmail.com';
		// $to = "pankaj.sevlani@abcd.com";
    	$custJsonData = array("sender"=>array( "name"=>"Ring-sancy","email"=>"donotreply@sancyberhad.com"),
    		"to"=>array(array("name"=>$to, "email"=>$to)),
            
    		"subject"=> $subject,
    		"htmlContent"=> $emailMessage
    	);  

    	$curl = curl_init();

    	curl_setopt_array($curl, array(
    		CURLOPT_URL => $url,
    		CURLOPT_RETURNTRANSFER => true,
    		CURLOPT_ENCODING => '',
    		CURLOPT_MAXREDIRS => 10,
    		CURLOPT_TIMEOUT => 0,
    		CURLOPT_FOLLOWLOCATION => true,
    		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    		CURLOPT_CUSTOMREQUEST => 'POST',
    		CURLOPT_POSTFIELDS => json_encode($custJsonData),

    		CURLOPT_HTTPHEADER =>   $headers,
    	));

    	$response = curl_exec($curl);
 		curl_close($curl);
    	$res = json_decode($response);
	}

	static function callSendMailWithAttachmentNew(){
		$url = "https://api.sendinblue.com/v3/smtp/email";

    	$headers = array('api-key: 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',
    		'Content-Type: application/json');

    	$custJsonData = array("sender"=>array( "name"=>"Ring-sancy","email"=>"donotreply@sancyberhad.com"),
    		"to"=>array(array("name"=>"mishraravi520@gmail.com", "email"=>"mishraravi520@gmail.com")),
            
    		"subject"=> "subject",
			'attachmentUrl' =>"http://win.k2key.in/Ring/upload/Ring_251_30-09-2022_Backup.zip",
    		"htmlContent"=> "message"
    	);  

    	$curl = curl_init();

    	curl_setopt_array($curl, array(
    		CURLOPT_URL => $url,
    		CURLOPT_RETURNTRANSFER => true,
    		CURLOPT_ENCODING => '',
    		CURLOPT_MAXREDIRS => 10,
    		CURLOPT_TIMEOUT => 0,
    		CURLOPT_FOLLOWLOCATION => true,
    		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    		CURLOPT_CUSTOMREQUEST => 'POST',
    		CURLOPT_POSTFIELDS => json_encode($custJsonData),

    		CURLOPT_HTTPHEADER =>   $headers,
    	));

    	$response = curl_exec($curl);
 		curl_close($curl);
    	$res = json_decode($response);
	}


}