<?php

   if($agreement_type)
   {
	   header('Location:?'.addOrReplaceGetOnURL('section='.$agreement_type.'_agr_setting'));
   }
   else if($agreement_id)
   {
       header('Location:?'.addOrReplaceGetOnURL('section=prepayment').'&agreement_type=long_term');
   }

?>
