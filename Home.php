<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	public function __construct(){
		parent::__construct();
		Header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
		Header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
		Header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
		$this->load->model('admin/location_model', 'location_model');
		$this->load->model('admin/user_model', 'user_model');
		$this->load->model('Home_model', 'home');
		$this->load->library('datatable'); // loaded my custom serverside datatable library
		$this->load->model('auth_model');
		$this->load->model('Product_model');
		$this->load->model('cms_model');
		$this->load->model('Category_model');
		$this->load->model('Blog_model');
		
		$this->load->helper('pdf_helper'); // loaded pdf helper
	}

	public function index($id=null)
	{
		$data['homecats'] =$category_details= $this->Category_model->getallcategories();
		$default_category_id = $category_details['0']['id'];
		$data['homedtls'] = $this->Category_model->getalldtls($default_category_id);
		//echo $this->db->last_query();
		$data['homeabout'] = $this->cms_model->getallcms('1');	
		$data['homeblog'] = $this->Blog_model->getallhomeblog($id);
		$data['social_media'] = 1;
		$this->load->view('frontend/includes/header',$data);
		$this->load->view('frontend/home/index',$data);
		$this->load->view('frontend/includes/footer');
	}
	public function warranty(){
		
	$data['homecats'] =$category_details= $this->Category_model->getallcategories();
	$this->load->view('frontend/includes/header',$data);
	$this->load->view('frontend/home/warrantydtls');
	$this->load->view('frontend/includes/footer');
	}

	public function login(){
		if($this->input->post('submit')){
			$this->form_validation->set_rules('email', 'Email', 'trim|valid_email|required');
			$this->form_validation->set_rules('password', 'password', 'trim|required');
			
			if ($this->form_validation->run() == FALSE) {
				$data = array(
					'errors' => validation_errors()
				);
				$this->session->set_flashdata('errors', $data['errors']);
				redirect(base_url('home/login'),'refresh');
			}
			else{
				$data = array(
					'email' => $this->input->post('email'),
					'password' => $this->input->post('password')
				);
				$data = $this->security->xss_clean($data);
				//pre($data);die;
				$result = $this->auth_model->login($data);
				
				if($result){
					if($result['is_active'] == 0){
						$this->session->set_flashdata('error', 'Account is disabled by Admin!');
						redirect(base_url('home/login'));
						exit();
					}
					$user_data = array(
						'user_id' => $result['id'],
						'firstname' => $result['firstname'],
						'is_login' => TRUE
					);
					$this->session->set_userdata($user_data);
					redirect(base_url('my_account'), 'refresh');
				}else{
					$this->session->set_flashdata('errors', 'Login failed');
					redirect(base_url('registration'));
				}
			}
		}else{
			$data['homecats'] =$category_details= $this->Category_model->getallcategories();
			$this->load->view('frontend/includes/header',$data);
			$this->load->view('frontend/home/login');
			$this->load->view('frontend/includes/footer');
		}
	}
	public function registration(){
		$this->load->library('email'); 
		if($this->input->post('submit')){
			$this->form_validation->set_rules('fullname', 'Fullname', 'trim|required');
			$this->form_validation->set_rules('password', 'Password', 'required');
			$this->form_validation->set_rules(
				'email', 'Email',
				'required|is_unique[users.email]',
				array(
						'required'      => 'You have not provided %s.',
						'is_unique'     => 'This %s already exists.'
				)
			);
			$this->form_validation->set_rules(
				'mobile', 'Mobile',
				'required|min_length[5]|is_unique[users.mobile_no]',
				array(
						'required'      => 'You have not provided %s.',
						'is_unique'     => 'This %s already exists.'
				)
			);
			if ($this->form_validation->run() == FALSE) {
				$data = array(
					'errors' => validation_errors()
				);
				$this->session->set_flashdata('errors', $data['errors']);
				redirect(base_url('registration'),'refresh');
			}
			else{
				$this->session->set_flashdata('errors', '');
				$data = array(
					'firstname' => $this->input->post('fullname'),
					'email' => $this->input->post('email'),
					'mobile_no' => $this->input->post('mobile'),
					'address' => $this->input->post('address'),
					'city' =>$this->input->post('city'),
					'state' =>$this->input->post('state'),
					'password' =>  password_hash($this->input->post('password'), PASSWORD_BCRYPT),
					'created_at' => date('Y-m-d : h:m:s'),
					'updated_at' => date('Y-m-d : h:m:s'),
				);
				$data = $this->security->xss_clean($data);
				$result = $this->user_model->add_user($data);
				if($result){
					//$this->activity_model->add_log(1);
					$config_mail = array(        
						'mailtype'  => 'html', 
						'charset' => 'utf-8',
						'wordwrap' => TRUE
					);
					$this->email->initialize($config_mail);
					$this->email->from('no-reply@toronto.com', 'Toronto Bicycles'); 

					$this->email->subject("Congratulations, Your registration is completed"); 
					$msg = "<img src='".base_url()."assets/img/logo.jpg' width='150px'><br><br>";
					$msg .= "Dear user,<br>you have registered successfully with us. Your login credentials are, email: ".$this->input->post('email')." and password:".$this->input->post('password');
					$msg.="	<br><br>Regards,<br><br><b>Team Toronto</b>";
					//echo $msg;exit;
					$this->email->message($msg);
					$this->email->to($this->input->post('email'));
					if($this->email->send()) {
						echo "Mail Sent";
					}else{
						echo "not sent";die;
					}
					$this->session->set_flashdata('success', 'User has been added successfully!');
					redirect(base_url('login'));
				}
			}
		}
		else{
			$data['state'] = $this->location_model->get_states_list();
			$data['homecats'] =$category_details= $this->Category_model->getallcategories();
			$this->load->view('frontend/includes/header',$data);
			$this->load->view('frontend/home/user-registration',$data);
			$this->load->view('frontend/includes/footer');
		}
	}
	public function dealer_registration(){
		if($this->input->post('submit')){
			$this->form_validation->set_rules('fullname', 'Fullname', 'trim|required');
			//$this->form_validation->set_rules('password', 'Password', 'required');
			$this->form_validation->set_rules(
				'email', 'Email',
				'required|is_unique[dealers.email]',
				array(
						'required'      => 'You have not provided %s.',
						'is_unique'     => 'This %s already exists.'
				)
			);
			$this->form_validation->set_rules(
				'mobile', 'Mobile',
				'required|min_length[5]|is_unique[dealers.mobile_no]',
				array(
						'required'      => 'You have not provided %s.',
						'is_unique'     => 'This %s already exists.'
				)
			);
			if ($this->form_validation->run() == FALSE) {
				$data = array(
					'errors' => validation_errors()
				);
				$this->session->set_flashdata('errors', $data['errors']);
				redirect(base_url('dealer_registration'),'refresh');
			}
			else{
				//pre($this->input->post());
				$this->session->set_flashdata('errors', '');
				$data = array(
					'username' => $this->input->post('username'),
					'firstname' => $this->input->post('fullname'),
					//'lastname' => $this->input->post('lastname'),
					'email' => $this->input->post('email'),
					'mobile_no' => $this->input->post('mobile'),
					'address' => $this->input->post('address'),
					'business_type'=>strtolower($this->input->post('business_type')),
					'firm_name'=>strtolower($this->input->post('firm_name')),
					'gst' => $this->input->post('gst'),
					//'latitude' => $this->input->post('latitude'),
					//'longitude' => $this->input->post('longitude'),
					//'credit_period' => $this->input->post('credit_period'),
					//'client_type' => $this->input->post('client_type'),
					//'client_category' => $this->input->post('client_category'),
					'password' =>  password_hash($this->input->post('password'), PASSWORD_BCRYPT),
					'created_at' => date('Y-m-d : h:m:s'),
					'updated_at' => date('Y-m-d : h:m:s'),
				);
				$data = $this->security->xss_clean($data);
				//pre($data);die;
				$result = $this->user_model->add_dealer($data);
				//echo q();die;
				//$result = $this->user_model->add_user($data);
				if($result){
					$address_details = array(
						'adress' => $this->input->post('location_act_name'),
						'latitude' => $this->input->post('latitude'),
						'longitude' => $this->input->post('longitude'),
						'zip' => $this->input->post('zip'),
						'dealer_id' => $this->db->insert_id(),
					);
					$address_details = $this->security->xss_clean($address_details);
					$res = $this->user_model->add_address($address_details);
					//$this->activity_model->add_log(1);
					$this->session->set_flashdata('success', 'User has been registered successfully!');
					redirect(base_url('dealer_registration'));
				}
			}
		}
		else{
			$data['state'] = $this->location_model->get_states_list();
			$data['homecats'] =$category_details= $this->Category_model->getallcategories();
			$this->load->view('frontend/includes/header',$data);
			$this->load->view('frontend/home/dealer-registration',$data);
			$this->load->view('frontend/includes/footer');
		}
	}
	public function site_lang($site_lang) {
		echo $site_lang;
		echo '<br>';
		echo 'you will be redirected to :'.$_SERVER['HTTP_REFERER'];
		$language_data = array(
			'site_lang' => $site_lang
		);

		$this->session->set_userdata($language_data);
		if ($this->session->userdata('site_lang')) {
			echo 'user session language is = '.$this->session->userdata('site_lang');
		}
		redirect($_SERVER['HTTP_REFERER']);

		exit;
	}
	public function logout(){
		$this->session->sess_destroy();
		redirect(base_url('login'), 'refresh');
	}
	public function my_account(){
		authenticate();
		if(!empty($this->input->post())){
			//pre($this->input->post());die;
			$config = array(
				'upload_path' => "./uploads/docs",
				'allowed_types' => "*",		
			);

			$config['max_size'] = '0';
			$config['encrypt_name'] = TRUE;
			$this->load->library('upload', $config);
			if($this->upload->do_upload('document'))
			{
				$data = array('upload_data' => $this->upload->data());
				$primaryData = $this->upload->data();
				$doc_name = $primaryData['file_name'];
			}else{
				echo $output['message']= $this->upload->display_errors();die;
			}
			$data = $this->security->xss_clean($this->input->post());
			$data = array("document_path"=>$doc_name,'user_id'=>$this->session->userdata('user_id'),"retail_partner"=>$data['retail_partner'],"purchase_date"=>$data['purchase_date'],"chasis_no"=>$data['chasis_no'],"model"=>$data['model'],"color"=>$data['color'],"wheel_size"=>$data['wheel_size'],"frame_size"=>$data['frame_size']);
			$result = $this->user_model->add_doc($data);
			if($result){
				//$this->activity_model->add_log(1);
				$this->session->set_flashdata('success', 'User has been added successfully!');
				redirect(base_url('my_account'));
			}

		}else{
			$data['datas'] = $this->user_model->get_docs($this->session->userdata('user_id'));
			$data['homecats'] =$category_details= $this->Category_model->getallcategories();
			$data['dealer_details'] = $this->user_model->get_all_dealers();
			$this->load->view('frontend/includes/header',$data);
			$this->load->view('frontend/home/account',$data);
			$this->load->view('frontend/includes/footer');
		}
	}
	public function store_locator(){
		$data['dealer_details'] = $this->user_model->get_all_dealers();
		//print q();die;
		
		$data['dealer_details'] = $this->user_model->get_all_dealers();
		$arr = array();
		$i = 0;
		foreach($data['dealer_details'] as $k=>$v){
			$latitude = $v['latitude'];
			$longitude = $v['longitude'];
			//$geocode = file_get_contents("http://maps.google.com/maps/api/geocode/json?latlng=$latitude,$longitude");
			// $geocode=file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyCioT0c99r6mKpqMQCuE7M0yjsvgj_ucjM&latlng='.$latitude.','.$longitude.'&sensor=false');
			// $json = json_decode($geocode);
			//echo "<pre>";print_r($json);die;
			// foreach($json->results[0]->address_components as $adr_node) {
			// 	if($adr_node->types[0] == 'postal_code') {
			// 		$postal =  $adr_node->long_name;
			// 	}elseif($adr_node->types[0] == 'locality'){
			// 		$city =  $adr_node->long_name;
			// 	}elseif($adr_node->types[0] == 'country'){
			// 		$country =  $adr_node->short_name;
			// 	}
				
			// }

			$arr[$i]['recommendation'] = array();
			$arr[$i]['storeNumber'] = $v['gst'];
			$arr[$i]['id'] = $v['id'];
			$arr[$i]['name'] = $v['firstname'];
			$arr[$i]['phoneNumber'] = $v['mobile_no'];
			$arr[$i]['coordinates']['latitude'] = $v['latitude'];
			$arr[$i]['coordinates']['longitude'] = $v['longitude'];
			$arr[$i]['regulations'] = array();
			$arr[$i]['address']['streetAddressLine1'] = $v['store_address'];
			$arr[$i]['address']['streetAddressLine2'] = '';
			$arr[$i]['address']['streetAddressLine3'] = '';
			$arr[$i]['address']['city'] = !empty($city)?$city:'';
			$arr[$i]['address']['countrySubdivisionCode'] = '';
			$arr[$i]['address']['countryCode'] = !empty($country)?$country:'';
			$arr[$i]['address']['postalCode'] = $v['zip'];
			$arr[$i]['addressLines'][] = $v['firstname'];
			$arr[$i]['addressLines'][] = $v['store_address'];
			$arr[$i]['mop']['ready'] = '';
			$arr[$i]['mop']['wait'] = '';
			// $text = $v['firstname'].''.$v['store_address'].''.$city;
			$text = $v['firstname'].''.$v['store_address'];
			$text = !empty($postal) ? $text.' '.$postal:$text; 
			$arr[$i]['slug'] = $this->slugify($text);
			$i++;
		}
		$data['json'] = json_encode($arr);
		$data['homecats'] =$category_details= $this->Category_model->getallcategories();
		//echo "<pre>";pre($arr);die;
		$this->load->view('frontend/includes/header',$data);
		$this->load->view('frontend/home/store_locator',$data);
		$this->load->view('frontend/includes/footer',$data);
	}
	public static function slugify($text, string $divider = '-')
	{
	// replace non letter or digits by divider
		$text = preg_replace('~[^\pL\d]+~u', $divider, $text);

		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);

		// trim
		$text = trim($text, $divider);

		// remove duplicate divider
		$text = preg_replace('~-+~', $divider, $text);

		// lowercase
		$text = strtolower($text);

		if (empty($text)) {
			return 'n-a';
		}

		return $text;
	}
	public function get_product_by_cat(){
		$get_products = $this->Category_model->getalldtls($this->input->post('cat_id'));
		//echo $this->db->last_query(); die;
		$html = '<div class="owl-carousel owl-theme showcase-slide">';
		foreach($get_products as $v){
			
				$html .= '<a class="showcase-item" href="'. base_url('product/details/'.$v['id']).'">';
				$html .='<span class="bg-span"></span>';
				$html .='<div class="img-holder">';
				$html .='<img class="img-fluid" src="'.base_url().'uploads/product/'. $v['image'].'" alt="">';
				$html .='</div>';
				$html .='<div class="showcase-pordDtl">';
				$html .='<h5 class="item-name">';
				$html .=$v['product_name'];
				$html .='</h5>';

				$html .='<p class="item-dtl">';
				$html .=$v['fork'];
				$html .='</p>'; 	

				$html .='</div>';
				$html .='</a>';	
		}
		$html .='</div>';
		echo $html;
	}

	public function get_place_search_list()
	{
		if($this->input->post('search'))
		{
			$search = $this->input->post('search');
			$result = $this->search_result($search);
			echo $result;
		}
	}

	public function get_store_list()
	{
		if($this->input->post())
		{
			$data = json_decode($this->input->post('data'),1);
			$lat = $data['lat'];
			$long = $data['lng'];
			$neLat = $data['neLat'];
			$neLng = $data['neLng'];
			$swLat = $data['swLat'];
			$swLng = $data['swLng'];
			$origins = $lat.','.$long;
			$stores = $this->home->get_store_list($neLat, $neLng, $swLat, $swLng);
			if(is_array($stores) && count($stores) > 0)
			{
				$destinations = '';
				for($i = 0; $i < count($stores); $i++)
				{
					$latitude = $stores[$i]['latitude'];
					$longitude = $stores[$i]['longitude'];
					$destinations .= $latitude.','.$longitude.'|';
				}
				$distances = json_decode($this->calculate_distance($origins,trim($destinations,'|')),true);
				$elements = $distances['rows'][0]['elements'];
				// pre($distances);
				if(isset($elements[0]['distance']))
				{
					for($i = 0; $i < count($elements); $i++)
					{
						$stores[$i]['distance']['text'] = $elements[$i]['distance']['text'];
						$stores[$i]['distance']['value'] = $elements[$i]['distance']['value'];
					}

					$newarr = array();

					foreach($stores as $key=>$v)
					{
					    $newarr[$v['distance']['value']][] = $v['firstname'];
					    $newarr[$v['distance']['value']][] = $v['adress'];
					    $newarr[$v['distance']['value']][] = $v['latitude'];
					    $newarr[$v['distance']['value']][] = $v['longitude'];
					    $newarr[$v['distance']['value']][] = $v['mobile_number'];
					    $newarr[$v['distance']['value']][] = $v['distance']['text'];
					}

					ksort($newarr);
					$refine_array = array();
					$i = 0;
					foreach($newarr as $key => $val)
					{
						$refine_array[$i]['name'] = $val[0];
						$refine_array[$i]['address'] = $val[1];
						$refine_array[$i]['latitude'] = $val[2];
						$refine_array[$i]['longitude'] = $val[3];
						$refine_array[$i]['mobile_number'] = $val[4];
						$refine_array[$i]['distance_text'] = $val[5];
						$refine_array[$i]['distance_value'] = $key;
						$i++;
					}

					echo json_encode($refine_array);
				}
				else
				{
					echo "not_found";
				}
			}
			else
			{
				echo "not_found";
			}
			
			
		}
	}

	public function calculate_distance($origins, $destinations)
	{	
		$key = "AIzaSyCioT0c99r6mKpqMQCuE7M0yjsvgj_ucjM";
		$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=${origins}&destinations=${destinations}&key=${key}";
		// echo "<br>";
		$jsondata = file_get_contents($url);
		return $jsondata;
	}

	public function get_search_list()
	{
		if($this->input->post())
		{
			$val = $this->input->post('val');
			$url = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input=${val}&types=geocode&key=AIzaSyCioT0c99r6mKpqMQCuE7M0yjsvgj_ucjM";

			if(($jsondata = @file_get_contents($url)) == false)
			{
				echo 0;
			}
			else
			{
				echo $jsondata;
			}
		}
	}

	public function get_place_details_by_id()
	{
		if($this->input->post())
		{
			$id = $this->input->post('id');
			$url = "https://maps.googleapis.com/maps/api/place/details/json?place_id=${id}&key=AIzaSyCioT0c99r6mKpqMQCuE7M0yjsvgj_ucjM";
			$jsondata = file_get_contents($url);
			echo $jsondata;
		}
	}
}
