<?php
class NameFixer {
  const EXTRA_CHAR_ERROR = 2;
  // Set up object that fixes names
  private $ext = array(
    // Matches "Jr" along with some permutations
    "/\,*\s*[jJ]{1}[rR]{1}\s*\.{0,1}/", 
    // Matches "C.S.C. Reverend" along with some permutations
    "/[cC]{1}\s*\.*\s*[sS]{1}\s*\.*\s*[cC]{1}\.*\s*[Rr]{1}[eE][vV](\.{0,1}|[eE][rR][eE][nN][dD])/", 
    // Matches "II" or "III"
    "/\s+,*\s*[iI]{2,3}/" 
  );
  private $remove = array(
    // All regexes in this array will be removed from the name
    "/\b[Cc]{1}hairman\b/",
    "/\b[dD]{1}irector\b/",
    "/\b[oO]{1}f\b/",
    "/\b[gG]{1}raduate\b/",
    "/\b[sS]{1}tudies\b/",
    "/\b[aA]{1}dvisor\b/",
    "/\b[fF]{1}aculty\b/",
    "/\b[oO]{1}ther\b/",
    "/\b[oO]{1}ffering\b/",
    "/\b[Ii]{1}nstruction\b/",
    "/\b[iI]{1}n\b/",
    "/\b[pP]{1}rofessor\b/",
    "/\b[nN]{1}[eE]{1}[lL]{1}[cC]{1}\b/",
    "/\b[aA]{1}nd\b/",
    "/\b[eE]{1}nglish\b/",
    "/\b[vV]{1}isual\b/",
    "/\b[eE]{1}nvironmental\b/",
    "/\b[mM]{1}\s*\.\s*[dD]\s*\.\s*\b/",
    "/\:{1,}/",
    "/\b[dD]{1}epartment\b/",
    "/\b[sS]{1}lavic\b/",
    "/\b[lL]{1}anguages\b/",
    "/\&/",
    "/\b[lL]{1}iterature[s]{0,}\b/"
  );
  // This is the main function! It takes a name and runs all of the other necessary checks for a name. Call it by using ->properize($name)
  public function properize($name){
    if ( stripos($name,",") !== false && count(explode(" ",$name)) < 5 ){
      $splitName = explode(",",$name);
      $name = preg_replace("/\s{2,}/"," ",trim($splitName[1]." ".$splitName[0]));
    }
    $name = $this->removeNonNameWords($name);
    $name = $this->removeIAAS($name);
    $name = $this->removeSpaceBeforeComma($name);
    $name = $this->removeAllAfterToken($name,"|");
    $name = $this->nameExt($name);
    $name = $this->lower($name);
    $name = $this->capitalizeHyphenatedName($name);
    preg_match("/\S+/u",$name,$check);
    if ( count($check) == 0 )
      return FALSE;
    else {
      preg_match("/[[:alpha:]\(\)\s\'\"\-\.\,\pL]{0,}/u",$name,$check);
      // Check to see if the name is valid e.g. no numbers and no extraneous characters
      if ( strlen($check[0]) != strlen($name) ){
	echo $check[0] . " --> " . $name;exit;
      }
      else
	return $name;
    }
  }
  public function removeNonNameWords($name){
    for ( $i = 0, $n = count($this->remove); $i < $n; $i++ ){
      $name = preg_replace($this->remove[$i],"",$name);
    }
    $name = trim($name);
    return $name;
  }
  public function removeIAAS($name){
    // This function is used to remove the accronym IAAS from the name. The IAAS is an accronym for a department in Harvard University
    $name = trim(preg_replace("/[iI]{1}[Aa]{2}[sS]{1}/","",$name));
    return $name;
  }
  public function lower($name){
    $names      = explode(" ",$name);
    $fixedName  = "";
    for ( $i = 0, $n = count($names); $i < $n; $i++ ){
      if ( stripos($names[$i],".") === false ){
        $fixedName .= ucfirst(strtolower($names[$i])) . " ";
      }
      else {
        $fixedName .= $names[$i] . " ";
      }
    }
    return $fixedName;
  }
  public function removeAllAfterToken($name,$token){
    $fixedName = explode($token,$name);
    return trim($fixedName[0]);
  }
  public function removeSpaceBeforeComma($name){
    return preg_replace("/\s+,/",",",$name); // Replace all spaces that are just before a comma ( changes "Stone , Jr." to "Stone, Jr.")
  }
  public function capitalizeHyphenatedName($name){
    $fixedName = $name;
    if ( stripos($name,"-") !== FALSE ){
      // Capitalize the first letter after the hyphen
      $names = explode("-",$name);
      $fixedName = implode("-",array($names[0],ucfirst($names[1])));
    }
    return $fixedName;
  }
  public function nameExt($name){
    for ( $i = 0, $n = count($this->ext); $i < $n; $i++ ){
      preg_match($this->ext[$i],$name,$matches);
      if ( empty($matches) === false ){
        $name = $this->{"_".$i}($name);
      }
    }
    return $name;
  }
  public function _0($name){
    $name = preg_replace($this->ext[0],"",$name) . ", Jr.";
    return $name;
  }
  public function _1($name){
    $name = "Rev. " . preg_replace($this->ext[1],"",$name) . ", C.S.C.";
    return $name;
  }
  public function _2($name){
    preg_match($this->ext[2],$name,$num);
    $name = preg_replace($this->ext[2],"",$name) . ", " . str_replace("i","I",$num[0]);
    return $name;
  }
}
?>
