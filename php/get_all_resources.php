<?php 

include('/home/svetlana/www/beta-code/backend/sqlSearch/sqlNameSearch.php');

$name = "Colton";
print_r(name_search("$name","Course",array(
  "options"=>array(
    "match"=>true
  ),
  "fields"=>array(
    "School"=>"Faculty of Arts and Sciences",
    "University"=>"Harvard University"
  )
),"Faculty"));
print_r(name_search("$name","Funding",array(
  "options"=>array(
    "match"=>true
  ),
  "fields"=>array(
    "School"=>"Faculty of Arts and Sciences",
    "University"=>"Harvard University"
  )
),"FirstNamePI"));
print_r(name_search("$name","Thesis",array(
  "options"=>array(
    "match"=>true
  ),
  "fields"=>array(
    "School"=>"Faculty of Arts and Sciences",
    "University"=>"Harvard University"
  )
),"Advisor1"));

?>
