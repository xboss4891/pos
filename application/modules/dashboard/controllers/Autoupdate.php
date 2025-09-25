<?php
defined('BASEPATH') OR exit('No direct script access allowed');
@ini_set('memory_limit', '100M');
@ini_set('max_execution_time', 400);
@ini_set("allow_url_fopen", 1);

// SECURITY: Disabled external API calls to prevent security vulnerabilities
// define('MIN_VERSION', @file_get_contents('https://update.bdtask.com/bhojon/autoupdate/update_min_version'));
define('MIN_VERSION', '2.0'); // Fixed version
// define('MAX_VERSION', @file_get_contents('https://update.bdtask.com/bhojon/autoupdate/update_max_version'));
define('MAX_VERSION', '3.0'); // Fixed version

// define('UPDATE_URL','https://update.bdtask.com/bhojon/autoupdate');
define('UPDATE_URL',''); // Disabled external update URL
// define('UPDATE_INFO_URL','https://update.bdtask.com/bhojon/autoupdate/update_info');
define('UPDATE_INFO_URL',''); // Disabled external info URL
// CRM temporary path
define('TEMP_FOLDER', FCPATH .'temp' . '/');

class Autoupdate extends MX_Controller {
	
	private $tmp_update_dir;
	private $tmp_dir;

	public function __construct()
	{
		parent::__construct();
		$this->load->library('user_agent');
		$this->db->query('SET SESSION sql_mode = ""');
	
	}
	 public function index(){ 

        $data = array();

        // SECURITY: Disabled external API call
        // $data['latest_version']  = @file_get_contents(UPDATE_INFO_URL);
        $data['latest_version'] = '3.0'; // Fixed version - no external calls
        $data['current_version'] = $this->current_version();

        //Checking update available or not
        if ($data['current_version']==$data['latest_version']) {
            //Your Message
        }
        //compatible version 
        else if ($data['current_version'] >= MIN_VERSION && $data['current_version'] <= MAX_VERSION) {
            $data['message_txt'] = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Update available';

        }else{
            $data['exception_txt'] = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Latest version is not compatible with this version';
        }        
		$data['title'] = display('autoupdate'); 
		$data['module'] = "dashboard"; 
		$data['page']   = "autoupdate/autoupdate";  
		echo Modules::run('template/layout', $data); 

      

    }
    public function checkserver(){
			
			if(ini_get('allow_url_fopen')) {
        		echo 1;
			}
			else {
				echo 0;
			}
		}
    public function update()
    {
        // SECURITY: Update functionality disabled
        $this->session->set_flashdata('exception', 'Update functionality has been disabled for security reasons.');
        redirect('dashboard/autoupdate');
    }

    public function updatenow()
    {
        // SECURITY: Update functionality disabled
        header('HTTP/1.0 403 Forbidden');
        echo json_encode(array("Update functionality has been disabled for security reasons."));
        die;
    }

    
    private function database($file_path = null)
    {
        $sql_contents = @file_get_contents($file_path);
        $sql_contents = explode(";", $sql_contents);

        $result = $this->db->query($sql_contents);


        foreach($sql_contents as $query)
        {
            $pos = strpos($query, 'ci_sessions');
       
            if($pos == false)
            {
                $result = $this->db->query($query);
            }
            else
            {
                continue;
            }
        }

    }

    private function clean_tmp_files()
    {
        if (is_dir($this->tmp_update_dir)) {
            if (@!$this->delete_dir($this->tmp_update_dir)) {
                @rename($this->tmp_update_dir, $this->tmp_dir . 'delete_this_' . uniqid());
            }
        }
    }

	/**
	 * Return server temporary directory
	 * @return string
	**/
	private function get_temp_dir()
	{
	    if (function_exists('sys_get_temp_dir')) {
	        $temp = sys_get_temp_dir();
	        if (@is_dir($temp) && is_writable($temp)) {
	            return rtrim($temp, '/\\') . '/';
	        }
	    }

	    $temp = ini_get('upload_tmp_dir');
	    if (@is_dir($temp) && is_writable($temp)) {
	        return rtrim($temp, '/\\') . '/';
	    }

	    $temp = TEMP_FOLDER;
	    if (is_dir($temp) && is_writable($temp)) {
	        return $temp;
	    }

	    return '/tmp/';
	}

    /**
     * Delete directory
     * @param  string $dirPath dir
     * @return boolean
    **/
    private function delete_dir($dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                delete_dir($file);
            } else {
                unlink($file);
            }
        }
        if (rmdir($dirPath)) {
            return true;
        }

        return false;
    }

    private function current_version(){

        //Current Version
        $product_version = '';
        $path = FCPATH.'system/core/compat/lic.php'; 
        if (file_exists($path)) {
            
            // Open the file
            $whitefile = @file_get_contents($path);

            $file = fopen($path, "r");
            $i    = 0;
            $product_version_tmp = array();
            $product_key_tmp = array();
            while (!feof($file)) {
                $line_of_text = fgets($file);

                if (strstr($line_of_text, 'product_version')  && $i==0) {
                    $product_version_tmp = explode('=', strstr($line_of_text, 'product_version'));
                    $i++;
                }                
            }
            fclose($file);

            $product_version = trim(@$product_version_tmp[1]);
            $product_version = ltrim(@$product_version, '\'');
            $product_version = rtrim(@$product_version, '\';');

            return @$product_version;
            
        } else {
            //file is not exists
            return false;
        }
        
    }

    private function product_key(){

        //Current Version
        $product_key     = '';
        $path = FCPATH.'system/core/compat/lic.php'; 
        if (file_exists($path)) {
            
            // Open the file
            $whitefile = @file_get_contents($path);

            $file = fopen($path, "r");
            $j    = 0;
            $product_version_tmp = array();
            $product_key_tmp = array();
            while (!feof($file)) {
                $line_of_text = fgets($file);
 
                if (strstr($line_of_text, 'product_key') && $j==0) {
                    $product_key_tmp = explode('=', strstr($line_of_text, 'product_key'));
                    $j++;
                }                
            }
            fclose($file);

            $product_key = trim(@$product_key_tmp[1]);
            $product_key = ltrim(@$product_key, '\'');
            $product_key = rtrim(@$product_key, '\';');

            return @$product_key;
            
        } else {
            //file is not exists
            return false;
        }

    }
 public function download_backup() {
        $db_name = 'backup' . '.sql';
		$path='assets/data/backup/' . $db_name;
		if(!is_file($path)){
			$contents = 'This is a test!';          
			file_put_contents($file, $contents); 
		}

        $this->load->dbutil();
        $prefs = array(
            'format'   => 'sql',
            'filename' => 'backup.sql');
        $b         = $this->dbutil->backup($prefs);
        $save      = 'assets/data/backup/' . $db_name;
        $this->load->helper('file');
        $username = $this->db->username;
        //----- Removing Security Hash FROM CREATE VIEW Queries
        $backup =  $b;
        //----- Commenting INSERT queries FOR VIEWS
        write_file($save, $backup);
        $this->load->helper('download');
        force_download('./assets/data/backup/' . $db_name, NULL);

    }
public function notifyoff(){
			$version=$this->input->post('version',true);
			$setdata = array(
				   'version'           => $version
				  );
		        $this->db->where('vid',1);
				$this->db->update('tbl_version_checker',$setdata);
	}

}