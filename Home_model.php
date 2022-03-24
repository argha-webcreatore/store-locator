<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Home_model extends CI_Model{
    
   public function get_store_list($neLat, $neLng, $swLat, $swLng)
   {
      $this->db->select('dealers.id, store_locator.latitude, store_locator.longitude as longitude, dealers.firstname as firstname, dealers.mobile_no as mobile_number, dealers.address as adress');
      $this->db->from('store_locator');
      $this->db->join('dealers','store_locator.dealer_id = dealers.id', 'right');
      $this->db->where("store_locator.latitude BETWEEN ${swLat} AND ${neLat} AND store_locator.longitude between ${swLng} AND ${neLng}");
      $result = $this->db->get()->result_array();

      return $result;
   } 



   // public function get_store_list($lat, $lng)
   // {
   //    $this->db->select("dealers.id, store_locator.latitude, store_locator.longitude as longitude, dealers.firstname as firstname, dealers.mobile_no as mobile_number, dealers.address as adress, (((acos(sin((${lat}*pi()/180)) * sin((`latitude`*pi()/180)) + cos((${lat}*pi()/180)) * cos((`store_locator`.`latitude`*pi()/180)) * cos(((${lng}- `store_locator`.`longitude`) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344) as distance");
   //    $this->db->from('store_locator');
   //    $this->db->join('dealers','store_locator.dealer_id = dealers.id', 'right');
   //    $this->db->having('distance < 10');
   //    $result = $this->db->get()->result_array();
   //    return $result;
   // } 

   


      
}    



      
    





















 