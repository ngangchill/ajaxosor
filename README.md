# Ajaxosor
Optimize your ajax' calls with CodeIgniter 


Create the file /application/libraries/Ajaxosor.php in your codeigniter's project 
Add the following code in it

<?php defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'/third_party/ajaxosor/Ajaxasor.php');


UTILISATION 

   public function ajax_show_followers()
    {
        $config = array(
            array('field' => 'iuser', 'label' => '', 'rules' => 'trim|min_length[10]|max_length[1000]', 'transformation' => 'decrypt='.$this->salt.'|object=\gmb\user_model'),
        );

        $this->ajaxosor->process($config, "library", "user_lib", "draw_followers");
    }
