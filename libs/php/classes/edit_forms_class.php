 <?php



 class Form_editor{
 	// исполнитель услуг по правам
    
	
	function __construct(){

		$this->user_id = $_SESSION['access']['user_id'];

		$this->user_access = $this->get_user_access_Database_Int($this->user_id);

		// обработчик AJAX
		if(isset($_POST['AJAX'])){
			$this->_AJAX_();
		}
	}

	//////////////////////////
	//	старые методы
	//////////////////////////

				public function get_user_access_Database_Int($id){
					global $mysqli;
					$query = "SELECT `access` FROM `".MANAGERS_TBL."` WHERE id = '".$id."'";
					$result = $mysqli->query($query) or die($mysqli->error);				
					$int = 0;
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$int = (int)$row['access'];
						}
					}
					//echo $query;
					return $int;
				}

				


	// ТРАНСЛИТЕРАЦИЯ
	public function GetInTranslit($string) {
		$replace=array(
			"'"=>"",
			"`"=>"",
			"а"=>"a","А"=>"a",
			"б"=>"b","Б"=>"b",
			"в"=>"v","В"=>"v",
			"г"=>"g","Г"=>"g",
			"д"=>"d","Д"=>"d",
			"е"=>"e","Е"=>"e",
			"ж"=>"zh","Ж"=>"zh",
			"з"=>"z","З"=>"z",
			"и"=>"i","И"=>"i",
			"й"=>"y","Й"=>"y",
			"к"=>"k","К"=>"k",
			"л"=>"l","Л"=>"l",
			"м"=>"m","М"=>"m",
			"н"=>"n","Н"=>"n",
			"о"=>"o","О"=>"o",
			"п"=>"p","П"=>"p",
			"р"=>"r","Р"=>"r",
			"с"=>"s","С"=>"s",
			"т"=>"t","Т"=>"t",
			"у"=>"u","У"=>"u",
			"ф"=>"f","Ф"=>"f",
			"х"=>"h","Х"=>"h",
			"ц"=>"c","Ц"=>"c",
			"ч"=>"ch","Ч"=>"ch",
			"ш"=>"sh","Ш"=>"sh",
			"щ"=>"sch","Щ"=>"sch",
			"ъ"=>"","Ъ"=>"",
			"ы"=>"y","Ы"=>"y",
			"ь"=>"","Ь"=>"",
			"э"=>"e","Э"=>"e",
			"ю"=>"yu","Ю"=>"yu",
			"я"=>"ya","Я"=>"ya",
			"і"=>"i","І"=>"i",
			"ї"=>"yi","Ї"=>"yi",
			"є"=>"e","Є"=>"e"
		);

		$text = iconv("UTF-8","UTF-8//IGNORE",strtr($string,$replace));
		// Удаляем знаки припенания 
		$text = preg_replace("|[^\d\w ]+|i","",$text); 
		// меняем пробелы на _
		$text = str_replace(" ", "_", trim($text)); 
		return $text;
	}

}


?>