<?php
/*****************************
css_format • scss to css
mit license • by albert kiteck
*****************************/
class css_format {
	public $return = [];
	public function scss($css,$returnArray=false,$addBreaks=false) {
		$css = $this->stripeWhitespace($css);
		$a = str_split($css);
		$content = [];
		$i = 0;
		//BREAK APART STRING TO ARRAYS
		foreach($a as $t) {
			if($t=="{") $content[$i] = trim($content[$i]);
			if($t=="{") $i++;
			if(!isset($content[$i])) $content[$i] = "";
			$content[$i].=$t;
			if($t=="}" || $t==";") $i++;
		}
		//LOOP ARRAY TO SET KEYS & VARS
		$keys = [];
		$dyn = $rep = [];
		$addCurl = 0;
		foreach($content as $n=>$row) {
			$row = trim($row);
			//SEE IF STRING IS $VAR - SET STR_REPLACE AND DON'T INCLUDE
			if(strpos($row,'$')===0 || strpos($row,'$')===1) {
				if(strpos($row,'{')===0) {
					$addCurl = 1;
					$row = substr($row, 1, -1);
				}
				
				$exp = explode(":",$row);
				$dyn[] = $exp[0];
				$rep[] = $exp[1];
				continue;
			}
			$prev = $n-1;
			if($addCurl){
				$row = "{".$row;
				$prev = $n-2;
				$addCurl = 0;
			}
			//IF BRACKET SET PREVIOUS AS KEY AND REMOVE BRACKET
			if(strpos($row,"{")===0) {
				$keys[] = $content[$prev];
				$row = substr($row, 1);
				$content[$n] = substr($content[$n], 1);
			}
			//IF CLOSE BRACKET REMOVE FROM LAST KEY FROM KEY ARRAY
			if($row=="}") array_splice($keys, count($keys)-1, 1); 
			
			if($keys) {
				$k = "";
				$q = "";
				foreach($keys as $n=>$ks) {
					$space = (isset($keys[$n+1]) && strpos($keys[$n+1],"&")===0) ? "" : " ";
					// IF & SYMBOL REMOVE SPACE
					if(strpos($ks,"&")===0) $ks = substr($ks, 1);
					//IF @ SYMBOL SHUFFLE KEYS TO ALLOW @ AS FIRST KEY
					if(strpos($ks,"@")===0) {
						$q = $ks;
					} else {
						$k.= $ks.$space;
					}
				}
				//IF $VARS REPLACE WITH VALUE
				if($dyn) $row = str_replace($dyn,$rep,$row);
				if($row!="}" && strpos($row,";")) {
					if($q!="") {
						$this->return[$q][$k][] = $row;
					} else {
						$this->return[$k][] = $row;
					}
					
				}
			}
			
		}
		return ($returnArray) ? $this->return : $this->render($addBreaks);
	}
	public function render($addBreak=0){
		$break = ($addBreak)  ? "<br>" : "";
		$space = ($addBreak)  ? "    " : "";
		$h = "";
		foreach($this->return as $k=>$o) {
			$h.= $k." {".$break;
			foreach($o as $k=>$v) {
				if(is_array($v)) {
					$h.= $space.$k." {".$break;
					foreach($v as $k1=>$v1) {
						$h.= $space.$space.$v1.$break;
					}
					$h.= $space."}".$break;
				} else {
					$h.= $space.$v.$break;
				}
			}
			$h.= "}".$break;
		}
		return $h;
	}
	public function stripeWhitespace($content) {
		return trim(str_replace(['	',"\r", "\n"], ['','',''], $content));
		return preg_replace('/\s+/', '', $content);
		
	}
}
?>