<?php


defined('MOODLE_INTERNAL') || die();
require($CFG->dirroot.'/user/profile/lib.php');
function GetUserProfileField($userid)
{
    global $DB;
    $newUser = $DB->get_record('user', array('id' => $userid));
    profile_load_custom_fields($newUser);
    return $newUser;
}
/**
 * phlstudent core course renderer renderer from the moodle core course renderer
 * @copyright  2015 onwards LMSACE Dev Team (http://www.lmsace.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class phl_csv{
    /**
     * @var int import identifier
     */
    private $_iid;
    
    /**
     * @var string which script imports?
     */
    private $_type;
    
    /**
     * @var string|null Null if ok, error msg otherwise
     */
    private $_error;
    
    /**
     * @var array cached columns
     */
    private $_columns;
    
    /**
     * @var object file handle used during import
     */
    private $_fp;
    
    /**
     * Contructor
     *
     * @param int $iid import identifier
     * @param string $type which script imports?
     */
    public function __construct($iid, $type) {
        $this->_iid  = $iid;
        $this->_type = $type;
    }
    
    /**
     * Make sure the file is closed when this object is discarded.
     */
    public function __destruct() {
        $this->close();
    }
    function switch_date_month($DateTime){
        $array = explode('/', $DateTime);
        $tmp = $array[0];
        $array[0] = $array[1];
        $array[1] = $tmp;
        unset($tmp);
        $result = implode('/', $array);
        return $result ;
    }
    
    public function phl_load_csv_content($content, $encoding, $delimiter_name,$mof_cohort, $column_validation=null, $enclosure='"') {
        global $USER, $CFG, $DB;
        
        $this->close();
        $this->_error = null;
        
        /*
         $content = core_text::convert($content, $encoding, 'utf-8');
         // remove Unicode BOM from first line
         $content = core_text::trim_utf8_bom($content);
         // Fix mac/dos newlines
         $content = preg_replace('!\r\n?!', "\n", $content);
         // Remove any spaces or new lines at the end of the file.
         if ($delimiter_name == 'tab') {
         // trim() by default removes tabs from the end of content which is undesirable in a tab separated file.
         $content = trim($content, chr(0x20) . chr(0x0A) . chr(0x0D) . chr(0x00) . chr(0x0B));
         } else {
         $content = trim($content);
         }
         */
        $csv_delimiter = csv_import_reader::get_delimiter($delimiter_name);
        $csv_encode    = csv_import_reader::get_encoded_delimiter($delimiter_name);
        
        // Create a temporary file and store the csv file there,
        // do not try using fgetcsv() because there is nothing
        // to split rows properly - fgetcsv() itself can not do it.
        $tempfile = tempnam(make_temp_directory('/csvimport'), 'tmp');
        if (!$fp = fopen($tempfile, 'w+b')) {
            $this->_error = get_string('cannotsavedata', 'error');
            @unlink($tempfile);
            return false;
        }
        fwrite($fp, $content);
        fseek($fp, 0);
        // Create an array to store the imported data for error checking.
        $columns = array();
        // str_getcsv doesn't iterate through the csv data properly. It has
        // problems with line returns.
        while ($fgetdata = fgetcsv($fp, 0, $csv_delimiter, $enclosure)) {
            // Check to see if we have an empty line.
            if (count($fgetdata) == 1) {
                if ($fgetdata[0] !== null) {
                    // The element has data. Add it to the array.
                    $columns[] = $fgetdata;
                }
            } else {
                $columns[] = $fgetdata;
            }
        }
        $col_count = 0;
        // process header - list of columns
        //var_dump($columns[0]);
        if (!isset($columns[0])) {
            $this->_error = get_string('csvemptyfile', 'error');
            fclose($fp);
            unlink($tempfile);
            
            return false;
        } else {
            //echo $columns[1][2];
            $area = $columns[2][1];
            $area= substr($area, strpos($area, ": ") + 1);
            $area = trim($area," "); // dia diem thi
            $arrage_date = $columns[1][4];
            $arrage_date = substr($arrage_date, strpos($arrage_date, ": ") + 1);
            $arrage_date = trim($arrage_date," "); // ngay thi
            $idnumber_arrange = $columns[1][1];
            $testcode = substr($idnumber_arrange, strpos($idnumber_arrange, ": ") + 1);
            $testcode = trim($testcode," "); // ma ky thi
            $check_testcode_exists = $DB->get_records_sql('select * from {mof_phl} where testcode=?', array('testcode' =>$whatIWant));
            foreach ($check_testcode_exists as $mytestcode) {
                $check_testcode_exist = $mytestcode->testcode;
            }
            
            $i = 4;
            $cohort_array = $DB->get_records_sql('select * from {cohort} where name=?', array('name' =>$mof_cohort));
            foreach ($cohort_array as $mymofcohort) {
                $MOF_Cohort_id = $mymofcohort->id;
            }
            if(empty($MOF_Cohort_id)){
                $Announce = "Cohort null";
            }
            else {
                $Announce = $mof_cohort;
            }
            echo "
<style>
.bubble{
   position:relative;
   padding: 10px;
   background: #FFCB41;
}
                
.bubble:after{
   position:absolute;
   content: '';
   top:15px;right:-10px;
   border-width: 10px 0 10px 15px;
   border-color: transparent  #FFCB41;
   border-style: solid;
 }Ã¢â‚¬â€¹
</style>
<p><span class='bubble'>Äiá»ƒm MOF Ä‘Ã£ Ä‘Æ°á»£c thÃªm má»›i</span></p>
";
            echo "
<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}
                
td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}
                
tr:nth-child(even) {
  background-color: #D9D9D9;
}
th {
  background-color: #EDAC00;
}
</style>
";
            echo "<table>
  <tr>
    <th style='color:white;'>Há» & TÃªn</th>
<th style='color:white;'>NgÃ y / ThÃ¡ng / NÄƒm</th>
<th style='color:white;'>CMTND</th>
<th style='color:white;'>NgÃ y cáº¥p</th>
<th style='color:white;'>NÆ¡i cáº¥p</th>
<th style='color:white;'>Äiá»ƒm</th>
<th style='color:white;'>Cohort</th>
<th style='color:white;'>KÃ½ tÃªn</th>
<th style='color:white;'>Ghi chÃº</th>
  </tr>
  ";
            do{
                $ExcelUser = $columns[$i][3];
                $sql_get_user = "
SELECT id as pop_id from mdl232x0_user WHERE username = N'$ExcelUser'
AND id IN (SELECT userid FROM mdl232x0_cohort_members WHERE cohortid = $MOF_Cohort_id)
";
                $get_user_array = $DB->get_records_sql($sql_get_user,array());
                foreach($get_user_array as $r)
                {
                    $ID_user_cohort[$i] = $r->pop_id;
                }
                if(empty($ID_user_cohort[$i])){
                    echo "<td bgcolor='red' style='color: white;'>User $ExcelUser khÃ´ng tá»“n táº¡i trong cohort $mof_cohort</td>";
                    echo "<td bgcolor='red' style='color: white;'></td>";
                    echo "<td bgcolor='red' style='color: white;'></td>";
                    echo "<td bgcolor='red' style='color: white;'></td>";
                    echo "<td bgcolor='red'></td>";
                    echo "<td bgcolor='red'></td>";
                    echo "<td bgcolor='red'></td>";
                    echo "<td bgcolor='red'></td>";
                    echo "<td bgcolor='red'></td>";
                    echo "</tr>";
                }
                else {
                    $a1 = $columns[$i][1];
                    $a2 = $columns[$i][2];
                    $a3 = $columns[$i][3];
                    $a4 = $columns[$i][4];
                    $a5 = $columns[$i][5];
                    $a6 = $columns[$i][8];
                    //
                    $array = explode('/', $arrage_date);
                    $tmp = $array[0];
                    $array[0] = $array[1];
                    $array[1] = $tmp;
                    unset($tmp);
                    $result = implode('/', $array);
                    $result1 = strtotime($result);
                    //
            
                    $vSql = "
                    select ngaysinh, thangsinh, namsinh
                    from vUser_Info
                    where cmnd = N'$ExcelUser'
";
                    $vUser_array = $DB->get_records_sql($vSql,array());
                    foreach($vUser_array as $v)
                    {
                        $vNgaysinh = $v->ngaysinh;
                        $vThangsinh = $v->thangsinh;
                        $vNamsinh = $v->namsinh;
                    }
                    
                    $update = GetUserProfileField($ID_user_cohort[$i]);
                    $db_birth_cer = $vNgaysinh.'/'.$vThangsinh.'/'.$vNamsinh;
                   
                    $ngaysinh = strtotime(switch_date_month($db_birth_cer));
                   
                    $ngaycapcmtnd = strtotime(switch_date_month($columns[$i][4]));
                    $ngaythi = strtotime(switch_date_month($arrage_date));
                    if(empty($a6)){
                        $a6 = 0;
                    }
                    $cmtnd_trans = $columns[$i][3];
                    $check_cmtnd = $DB->get_records_sql('select * from {mof_phl} where idnumber=?', array('idnumber' =>$cmtnd_trans));
                    foreach ($check_cmtnd as $x) {
                        $cmnd[$i] = $x->idnumber;
                    }
                    $user_array = $DB->get_records_sql('select * from {user} where username=?', array('username' =>$cmtnd_trans));
                    foreach ($user_array as $u) {
                        $a_man_firstname = $u->firstname;
                        $a_man_lastname = $u->lastname;
                        $fullname = $a_man_firstname." ".$a_man_lastname;
                    }
                    if(empty($cmnd[$i]))
                    {
                        echo "<tr>";
                        //echo "User not exist. Adding process.<br>";
                        //echo "Ngay sinh : ".date("d/m/Y", $ngaysinh)."<br>";
                        $mof->datetest = $ngaythi;
                        $mof->testcode = $testcode;
                        $mof->address = $area;
                        $mof->score = $a6;
                        $mof->dategranted = $ngaycapcmtnd;
                        $mof->full_name = $a1;
                        $mof->birthday = $ngaysinh;
                        $mof->idnumber = $cmtnd_trans;
                        $mof->iddate = $ngaycapcmtnd ;
                        $mof->idplace = $a5;
                        $mof->note = '';
                        if(!empty($MOF_Cohort_id)){
                            $mof->cohortid = $MOF_Cohort_id;
                        }
                        $mof->id = $DB->insert_record("mof_phl",$mof);
                        $check_item_array = $DB->get_records_sql('select * from {mof_phl} where id=?', array('id' =>$mof->id));
                        foreach ($check_item_array as $item) {
                            $idplace = $item->idplace;
                            $birthday = $item->birthday;
                            $iddate = $item->iddate;
                            $full_name = $item->full_name;
                            $idnumber = $item->idnumber;
                            $cohortid = $item->cohortid;
                        }
                        if(empty($idplace)||empty($birthday)||empty($iddate)
                            ||empty($full_name)||empty($idnumber)||empty($cohortid)){
                                $sql = "
                            update mdl232x0_mof_phl
                            set idplace = N'$a5',
                            birthday = $ngaysinh,
                            iddate = $ngaycapcmtnd,
                            full_name = N'$a1',
                            idnumber = N'$cmtnd_trans',
                            cohortid = $MOF_Cohort_id
                            where id = $mof->id";
                                $DB->execute($sql,array());}
                                
                                $ngaysinh = date("d/m/Y", $ngaysinh);
                                $ngaycapcmtnd = date("d/m/Y",$ngaycapcmtnd);
                                echo "<td>$fullname</td>";
                                echo "<td>$ngaysinh</td>";
                                echo "<td>$cmtnd_trans</td>";
                                echo "<td>$ngaycapcmtnd</td>";
                                echo "<td>$a5</td>";
                                echo "<td>$a6</td>";
                                echo "<td>$Announce</td>";
                                echo "<td></td>";
                                echo "<td></td>";
                                echo "</tr>";
                    }
                    else
                    {
                        $MOF_array = $DB->get_records_sql('select * from {mof_phl} where idnumber=?', array('idnumber' =>$cmtnd_trans));
                        foreach ($MOF_array as $n) {
                            $mofid = $n->id;
                        }
                        $sql = "
                            update mdl232x0_mof_phl
                            set idplace = N'$a5',
                            birthday = $ngaysinh,
                            iddate = $ngaycapcmtnd,
                            full_name = N'$a1',
                            idnumber = N'$cmtnd_trans'
                            where id = $mofid";
                        $DB->execute($sql,array());
                        echo "<tr>";
                        echo "<td>Há»c viÃªn $cmnd[$i] Ä‘Ã£ Ä‘Æ°á»£c cáº­p nháº­t</td>";
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "</tr>";
                        //echo "User exists. Ignored.<br>";
                    }
                    
                }
                //echo "<tr>";
                $i++;
                if ($columns[$i][1] == ''){
                    break;
                }
                
            }
            while(foo);
            echo "
</table>
<br>
";
            echo "
<style>
.button {
  background-color: #FFCB41; /* Green */
  border: none;
  color: white;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  margin: 4px 2px;
  cursor: pointer;
  -webkit-transition-duration: 0.4s; /* Safari */
  transition-duration: 0.4s;
}
                
.button1  {
  box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19);
}
                
.button2 {
  box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24),0 17px 50px 0 rgba(0,0,0,0.19);
}
#mform1{ display : none;}
</style>";
            echo "<form style='display:inline-block; width:45%;text-align:center;' action='mof_pick_cohort.php'><button class='button button1'>Back</button></form>";
            
            for($i=1;$i<count($columns);$i++) {
                
                
                $columns[$i][23]=$columns[$i][$cohortNameCol];
                /*
                 $columns[$i][$col_count+1]=$lastName;
                 $columns[$i][$col_count+2]='vi';
                 $columns[$i][$col_count+3]='allstudent';
                 */
                //var_dump($columns[$i]);
            }
            $col_count=count($columns[0]);
            //End Custom Code
            
        }
        
        // Column validation.
        if ($column_validation) {
            
            $result = $column_validation($columns[0]);
            if ($result !== true) {
                
                $this->_error = $result;
                fclose($fp);
                unlink($tempfile);
                return false;
            }
        }
        //var_dump( $columns[0]);
        $this->_columns = $columns[0]; // cached columns
        // check to make sure that the data columns match up with the headers.
        foreach ($columns as $rowdata) {
            //var_dump($rowdata);
            //echo count($rowdata)."XXX".$col_count;
            if (count($rowdata) !== $col_count) {
                
                $this->_error = get_string('csvweirdcolumns', 'error');
                fclose($fp);
                unlink($tempfile);
                $this->cleanup();
                return false;
            }
            break;
        }
        
        $filename = $CFG->tempdir.'/csvimport/'.$this->_type.'/'.$USER->id.'/'.$this->_iid;
        $filepointer = fopen($filename, "w");
        // The information has been stored in csv format, as serialized data has issues
        // with special characters and line returns.
        $storedata = csv_export_writer::print_array($columns, ',', '"', true);
        
        fwrite($filepointer, $storedata);
        
        fclose($fp);
        unlink($tempfile);
        fclose($filepointer);
        
        $datacount = count($columns);
        
        //var_dump($columns[0]);
        return $datacount;
    }
    /**
     * Returns list of columns
     *
     * @return array
     */
    public function get_columns() {
        if (isset($this->_columns)) {
            return $this->_columns;
        }
        
        global $USER, $CFG;
        
        $filename = $CFG->tempdir.'/csvimport/'.$this->_type.'/'.$USER->id.'/'.$this->_iid;
        echo "filename = ".$filename."<br>";
        if (!file_exists($filename)) {
            return false;
        }
        $fp = fopen($filename, "r");
        $line = fgetcsv($fp);
        fclose($fp);
        if ($line === false) {
            return false;
        }
        $this->_columns = $line;
        return $this->_columns;
    }
    
    /**
     * Init iterator.
     *
     * @global object
     * @global object
     * @return bool Success
     */
    public function init() {
        global $CFG, $USER;
        
        if (!empty($this->_fp)) {
            $this->close();
        }
        $filename = $CFG->tempdir.'/csvimport/'.$this->_type.'/'.$USER->id.'/'.$this->_iid;
        if (!file_exists($filename)) {
            return false;
        }
        if (!$this->_fp = fopen($filename, "r")) {
            return false;
        }
        //skip header
        return (fgetcsv($this->_fp) !== false);
    }
    
    /**
     * Get next line
     *
     * @return mixed false, or an array of values
     */
    public function next() {
        if (empty($this->_fp) or feof($this->_fp)) {
            return false;
        }
        if ($ser = fgetcsv($this->_fp)) {
            return $ser;
        } else {
            return false;
        }
    }
    
    /**
     * Release iteration related resources
     *
     * @return void
     */
    public function close() {
        if (!empty($this->_fp)) {
            fclose($this->_fp);
            $this->_fp = null;
        }
    }
    
    /**
     * Get last error
     *
     * @return string error text of null if none
     */
    public function get_error() {
        return $this->_error;
    }
    
    /**
     * Cleanup temporary data
     *
     * @global object
     * @global object
     * @param boolean $full true means do a full cleanup - all sessions for current user, false only the active iid
     */
    public function cleanup($full=false) {
        global $USER, $CFG;
        
        if ($full) {
            @remove_dir($CFG->tempdir.'/csvimport/'.$this->_type.'/'.$USER->id);
        } else {
            @unlink($CFG->tempdir.'/csvimport/'.$this->_type.'/'.$USER->id.'/'.$this->_iid);
        }
    }
    
    /**
     * Get list of cvs delimiters
     *
     * @return array suitable for selection box
     */
    public static function get_delimiter_list() {
        global $CFG;
        $delimiters = array('comma'=>',', 'semicolon'=>';', 'colon'=>':', 'tab'=>'\\t');
        if (isset($CFG->CSV_DELIMITER) and strlen($CFG->CSV_DELIMITER) === 1 and !in_array($CFG->CSV_DELIMITER, $delimiters)) {
            $delimiters['cfg'] = $CFG->CSV_DELIMITER;
        }
        return $delimiters;
    }
    
    /**
     * Get delimiter character
     *
     * @param string separator name
     * @return string delimiter char
     */
    public static function get_delimiter($delimiter_name) {
        global $CFG;
        switch ($delimiter_name) {
            case 'colon':     return ':';
            case 'semicolon': return ';';
            case 'tab':       return "\t";
            case 'cfg':       if (isset($CFG->CSV_DELIMITER)) { return $CFG->CSV_DELIMITER; } // no break; fall back to comma
            case 'comma':     return ',';
            default :         return ',';  // If anything else comes in, default to comma.
        }
    }
    
    /**
     * Get encoded delimiter character
     *
     * @global object
     * @param string separator name
     * @return string encoded delimiter char
     */
    public static function get_encoded_delimiter($delimiter_name) {
        global $CFG;
        if ($delimiter_name == 'cfg' and isset($CFG->CSV_ENCODE)) {
            return $CFG->CSV_ENCODE;
        }
        $delimiter = csv_import_reader::get_delimiter($delimiter_name);
        return '&#'.ord($delimiter);
    }
    
    /**
     * Create new import id
     *
     * @global object
     * @param string who imports?
     * @return int iid
     */
    public static function get_new_iid($type) {
        global $USER;
        
        $filename = make_temp_directory('csvimport/'.$type.'/'.$USER->id);
        
        // use current (non-conflicting) time stamp
        $iiid = time();
        while (file_exists($filename.'/'.$iiid)) {
            $iiid--;
        }
        
        return $iiid;
    }
    
}
