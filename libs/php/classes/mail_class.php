<?php
 class Mail{
	    public $added_headers = NULL;
		public $multipart = FALSE;
		public $boundary = NULL;
		public $message_parts = array();
	    function __consturct(){
		}
		public function add_bcc($to){
		    if(!$this->added_headers) $this->added_headers = array();
		    if(!is_array($to)) $to = array($to);
			//$headers .= "Bcc: box@yandex.ru \r\n"; 
			array_push($this->added_headers,"Bcc: ".implode(",",$to)." \r\n");
		}
		public function add_cc($to){
		    if(!$this->added_headers) $this->added_headers = array();
		    if(!is_array($to)) $to = array($to);
			//$headers .= "Cc: box@yandex.ru \r\n"; 
			array_push($this->added_headers,"Cc: ".implode(",",$to)." \r\n");
		}
		public function attach_file($filepath){
		    if(!$this->multipart) $this->multipart = TRUE;
		    if(empty($this->boundary)) $this->boundary = md5(uniqid(time()));
		    if(!$this->added_headers) $this->added_headers = array();
			array_push($this->added_headers,"Content-Type: multipart/mixed; boundary = \"".$this->boundary."\"\r\n");
			$filepath_utf = iconv("UTF-8","windows-1251", $filepath);
            $filename = substr($filepath,strrpos($filepath,"/")+1);
			
			if($fd = @fopen($filepath_utf,"rb")){

				$content = fread($fd,filesize($filepath_utf));
				$content = chunk_split(base64_encode($content));
				fclose($fd);
				unset($fd);
 
				$filename = "=?utf-8?b?".base64_encode($filename)."?=";
				$message = "Content-Type: application/pdf; name=\"".$filename."\"\r\n";
				$message .= "Content-Transfer-Encoding: base64\r\n";
				$message .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
				$message .= $content."\r\n\r\n";
				$this->message_parts[] = $message;
			}
			else $this->errors[] = 'Не удалось прочитать файл '.$filepath_utf.' с диска'; 
		}
		public function add_img_in_letter($filepath){
		    if(!$this->multipart) $this->multipart = TRUE;

			
			$filepath_utf = substr(ROOT,0,strrpos(ROOT,"/")).iconv("UTF-8","windows-1251", $filepath);
            $filename = substr($filepath,strrpos($filepath,"/")+1);
			
			//echo $filepath_utf;

			if($fd = @fopen($filepath_utf,"rb")){

				$content = fread($fd,filesize($filepath_utf));
				$content = chunk_split(base64_encode($content));
				fclose($fd);
				unset($fd);
                
				$filename = substr($filepath,strrpos($filepath,"/")+1);
				//$filename = "=?utf-8?b?".base64_encode($filename)."?=";
				
				$this->relatedparts = "Content-type: image/jpeg; name=\"".$filename."\"\r\n";
				$this->relatedparts .= "Content-Disposition: inline; filename=\"".$filename."\"\r\n";
				$this->relatedparts .= "Content-Transfer-Encoding: base64\r\n";
				$this->relatedparts .= "X-Attachment-Id: ii_14504dc8ea704442\r\n";
				$this->relatedparts .= "Content-ID: ii_14504dc8ea704442\r\n\r\n";
				
				//$message = "Content-type: application/octet-stream; name=\"".$filename."\"\r\n";
				//$message .= "Content-Transfer-Encoding: base64\r\n";
				//$message .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
				//$message .= "Content-ID: ".$filename."\r\n";
				$this->relatedparts .= $content."\r\n\r\n";
			}
			else $this->errors[] = 'Не удалось прочитать файл '.$filepath_utf.' с диска'; 
        }
	    public function send($to,$from,$subject,$message){
		    
		    if(empty($to))$this->errors[] = 'Не указан отправитель';
			if(empty($from))$this->errors[] = 'Не указан получатель'; 
			if(empty($subject))$this->errors[] = 'Не указана тема письма'; 
			if(empty($message))$this->errors[] = 'Письмо не содержит сообщения'; 


			$message = base64_decode($message);
			$message = urldecode($message);
			//$pattern = '/<img.+href=[\'\"](.+)[\'\"]/is';
			$pattern = '/<img.+src=[\'\"]{1}([^\'\"]+)[\'\"]{1}>/is';
			if(preg_match_all($pattern,$message,$matches)){
			    //print_r($matches); exit;
				//$message = str_replace($matches[0][0],'<img src="cid:'.substr($matches[1][0],strrpos($matches[1][0],"/")+1).'" />',$message);
				$message = str_replace($matches[0][0],'<img src="cid:ii_14504dc8ea704442" />',$message);
				
				$this->add_img_in_letter($matches[1][0]);
			}
			$message = iconv("UTF-8", "windows-1251", $message);
			
			$subject = "=?utf-8?b?".base64_encode($subject)."?=";
			
			
			
			if($this->multipart){
			    $message= "Content-Type:text/html; charset=\"windows-1251\"\r\n\r\n".$message."\r\n";
                if(isset($this->relatedparts)){
				     $boundary_related = md5(uniqid(time()));
					 $header = 'Content-Type: multipart/related; boundary='.$boundary_related."\r\n";
					 $message = $header."\r\n"."--".$boundary_related."\r\n".$message."\r\n"."--".$boundary_related."\r\n".$this->relatedparts."\r\n"."--".$boundary_related."--\r\n";
				}
			    
			    array_unshift($this->message_parts,$message);
			    $message =  "--".$this->boundary."\r\n".implode( "--".$this->boundary."\r\n",$this->message_parts). "--".$this->boundary."--\r\n";
			}
			
			$this->headers  = "MIME-Version: 1.0\r\n";
			$this->headers .= "Date: ". date('D, d M Y h:i:s O') ."\r\n";
			$this->headers .= "From: ".$from."\r\n";
			if(!$this->multipart) $this->headers .= "Content-Type:text/html; charset=\"windows-1251\"\r\n";
			if($this->added_headers) foreach($this->added_headers as $header) $this->headers .= $header;
			//echo $to."\r\n @ \r\n @ \r\n".$subject."\r\n @ \r\n @ \r\n".$message."\r\n @ \r\n @ \r\n".$this->headers;
			//exit;
			
			// если были ошибки прерываем дальшейшее выполнение функции
			if(!empty($this->errors)) return '[0,"'.implode("<br>",$this->errors).'"]';
			
			//if(mail($to,$subject,$message,$this->headers,"-f".$from)){ такой вариант почемуто не сработал
			//echo $message; exit;
			if(mail($to,$subject,$message,$this->headers)){
				 return '[1,"Cообщение отправлено"]';
			}
			else{
				 return '[0,"Cообщение не отправлено"]';
			}
	    }
			/////////////////////////////////////////////////////////////////////////////////////////////////////////
			////////////////////////////////////    SENDING LETTER      ////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////////////////////////////////////////////
		
			/*$boundary = md5(uniqid(time()));// разграничитель
			$boundary_2 = md5(uniqid(time()))."2";// разграничитель 2
		
			$headers  = "MIME-Version: 1.0\r\n";
			$headers .= "Date: ". date('D, d M Y h:i:s O') ."\r\n";
			$headers .= "Content-Type: multipart/mixed; boundary = \"".$boundary."\"\r\n";
			$headers .= "From: zakaz@vodkaspb.ru\r\n";
			$headers .= "Bcc: premier22@yandex.ru \r\n"; //
			
			
			
			$multipart  = "--".$boundary."\r\n";  
			   $multipart .= "Content-Type:multipart/alternative; boundary = \"".$boundary_2."\"\r\n"; 
			   $multipart .= "\r\n";
			   $multipart .= "--".$boundary_2."\r\n";
				  $multipart .= "Content-Type:text/plain; charset=windows-1251\r\n"; 
				  $multipart .= "\r\n"; // раздел между заголовками и телом plain-части 
				  $multipart .= $plain_content;   
				  $multipart .= "\r\n";
			   $multipart .= "--".$boundary_2."\r\n"; 
				  $multipart .= "Content-Type:text/html; charset=windows-1251\r\n";  
				  $multipart .= "\r\n"; // раздел между заголовками и телом html-части 
				  $multipart .= $html_content;   
				  $multipart .= "\r\n";
			   $multipart .= "--".$boundary_2."--\r\n";
			$multipart .= "--".$boundary."--\r\n";   
			
			
		*/
	}





 ?>
