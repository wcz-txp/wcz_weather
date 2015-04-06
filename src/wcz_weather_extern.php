<?php

// this plugin uses most of the code from
// snt_weather plugin
// by Simon Lindley
// http://www.simonaut.com/
// Version 0.2   - 11/06/2005
// 0.3 enhanced
// 0.3.1 Update 08/07/2009 API-Update
// 0.3.2 error handling
// 0.4.0 use of worldweatheronline api
// 0.5.0 new parameters, code cleaning, parameter "names" is not obligatory, different language strings
// 0.5.1 bugfixes, changed api-server


$names="Kyjiw";  
$codes="Kiev"; 
$image="1";
$sprite_image="1";
$key=""; 
$class="weather"; 
$row_class="row"; 
$place_class="place"; 
$temperature_class="temperature"; 
$icon_class="weather-sprite"; 
$wind_unit="k"; 
$temperature_unit="c"; 
$language="de"; 
$wait="300000"; 
$debug="0";
$forecast="1";
$filename="/var/www/your_site/textpattern/tmp/weather.html";




  $codes=explode(",",$codes);
  if (empty($names)) {
    $names=$codes;
  }
  else {    $names=explode(",",$names); }


  // language-part

  // German
  if ($language=="de"){
    $humidity="Luftfeuchtigkeit";
    $wind="Windgeschwindigkeit";
    $rowtext="Das Wetter in";
    $datatext="Daten von";
  }

  else {

    // Russian
    if ($language=="ru"){
      $humidity="влажность";
      $wind="скорость ветра";
      $rowtext="Погода в";
      $datatext="Данные от";
    }

    else {
      // Ukrainian
      if ($language=="ua"){
    $humidity="вологість";
    $wind="швидкість вітру";
    $rowtext="Погода в";
    $datatext="Дані від";
      }

      else {
    // English
    $humidity="humidity";
    $wind="wind speed";
    $rowtext="The weather in";
    $datatext="Data from";
      }
    }
  }

//wcz_weather($names,$codes,$image,$sprite_image,$key,$class,$row_class,$place_class,$temperature_class,$icon_class,$wind_unit,$temperature_unit,$language,$wait,$debug);

  class wcz_weather_class  {

    // function must have the same name as the class!

    function wcz_weather_class($loc_code,$key,$forecast,$debug,$wind_unit,$temperature_unit,$wait,$language) {
      $xml=simplexml_load_string(file_get_contents("http://api2.worldweatheronline.com/free/v1/weather.ashx?q=".$loc_code."&format=xml&num_of_days=".$forecast."&lang=".$language."&key=".$key));

      if ($debug == "1") {print_r($xml);}

      if($xml){

    if(isset($xml->error)) {
      $this->Temperature='-';
      $this->Icon='na';
      $this->Condition='-';
      $this->Humidity='-';
      $this->Wind='-';
    }

    else
    {

      if ($temperature_unit == "f") {
        $this->Temperature=htmlspecialchars($xml->current_condition->temp_F);
        $this->temperature_unit="F";
      }
      else {
        $this->Temperature=htmlspecialchars($xml->current_condition->temp_C);
        $this->temperature_unit="C";
      }

      if ($wind_unit == "m") {
        $this->Wind=htmlspecialchars($xml->current_condition->windspeedMiles);
        $this->wind_unit_table='mph';
      }
      else {
        $this->Wind=htmlspecialchars($xml->current_condition->windspeedKmph);
        $this->wind_unit_table='km/h';
      }

      $this->Icon=htmlspecialchars($xml->current_condition->weatherIconUrl);

      if ($language == "en") {
           $this->Condition=htmlspecialchars($xml->current_condition->weatherDesc);
          }
      else {
               $lang="lang_".$language;
               $this->Condition=htmlspecialchars($xml->current_condition->$lang);
          }

      $this->Humidity=htmlspecialchars($xml->current_condition->humidity);


      if ($debug == "1") {

        print_r($this->Temperature);
        print_r($this->Icon);
        print_r($this->Condition);
        print_r($this->Humidity);
        print_r($this->Wind);
        print_r($this->temperature_unit);
        print_r($this->wind_unit);
        print_r($this->wind_unit_table);

      }

    }
      }

      else
      {
    $this->Temperature='-';
    $this->Icon='na';
    $this->Condition='-';
    $this->Humidity='-';
    $this->Wind='-';
      }


      // end of the function
    }



    // end of the class
  }

  $Out=array();
  $output=array();

  foreach ($codes as &$loc_code) {
    $loc_code=urlencode(trim($loc_code));
    $output = new wcz_weather_class($loc_code,$key,$forecast,$debug,$wind_unit,$temperature_unit,$wait,$language);


    $title=$output->Condition ." - ". $humidity ." ". $output->Humidity ." % - ". $wind ." ". $output->Wind ." ". $output->wind_unit_table;

    if($image == "0") {

      $Out[] = '<td class="'. $temperature_class .'" title="'. $title .'">'. $output->Temperature .'&nbsp;&deg;'. $output->temperature_unit .'</td>';
            // Pause
      usleep($wait);
    }

    else {

      if($sprite_image == "1") {
    $icon = str_replace(array('http://www.worldweatheronline.com/images/wsymbols01_png_64/','.png'),'',substr(strrchr($output->Icon,'/'),1));
    $Out[] = '<td class="'. $temperature_class .'" title="'. $title .'">'. $output->Temperature .'&nbsp;&deg;'. $output->temperature_unit.'&nbsp;</td><td class="'. $icon_class .'" title="'. $title .'" id="'. $icon .'">&nbsp;</td>';
      // Pause
      usleep($wait);
      }

      else  {
    $Out[] = '<td class="'. $temperature_class .'" title="'. $title .'">'. $output->Temperature .'&nbsp;&deg;'. $output->temperature_unit .'&nbsp;</td><td title="'. $title .'"><img src="'. $output->Icon .'"  class="'. $icon_class .'" alt="'. weathericon .'" title="'. $title .'" /></td>';
          // Pause
      usleep($wait);
      }
    }

  }



  foreach($names as $location=>&$place) {
    $place=trim($place);
    $Out[$location] = '<td class="'. $place_class .'" title="'. $rowtext .' '. $place .'">'. $place .'</td>'. $Out[$location];
  }

//print_r($Out);

$rowCount = 0;

  $all='<table class="'. $class .'">';
  foreach ($Out as $a) {
  if ($rowCount++ % 2 == 1 ) {
    $all.=$a.'</tr>';
}
  else {

    $all.='<tr>'.$a;
  }
}


  $all=$all.'</tr></table>'.$datatext.' <a href="http://www.worldweatheronline.com/" title="Free Weather API" target="_blank">World Weather Online</a>';

  $file=fopen($filename, "w+");
  fwrite($file,$all);
  fclose($file);


?>