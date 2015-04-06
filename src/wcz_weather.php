<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'wcz_weather';

$plugin['version'] = '0.5.1';
$plugin['author'] = 'whocarez';
$plugin['author_uri'] = '';
$plugin['description'] = 'Shows a table of weather stations';
$plugin['type'] = 0; // 0 for regular plugin; 1 if it includes admin-side code
$plugin['order'] = 5; # use 5 as a default; ranges from 1 to 9


@include_once('zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

Just install and activate.

You have to register at "*World Weather Online*":http://worldweatheronline.com/register.aspx
You can find there icons in worldweather directory with 25px icons.
Use weather_sprite.css and weather-sprite.png in your layout, adapt it to your needs.
You can find a list of weather codes and the names of the icons "*here*":http://worldweatheronline.com/feed/wwoConditionCodes.txt
For city code look "*here*":http://www.worldweatheronline.com/country.aspx

*Please note, that you have to use a caching plugin like "*aks_cache*":http://textpattern.org.ua/plugins/aks_cache, to stay within the limit of 500 requests per hour!*
a second variant is to use the external version with cron or "*aks_cron*":http://textpattern.org.ua/plugins/aks_cron

Example use:
@<txp:weather names="Town_1,City_2" codes="town1,city2" key="your_key" />@

Parameters:
names: name of the city/town how you want to see it on your site, not obligatory, if not declared location code (see next line) is used
codes: location code of the city/town on worldweatheronline.com

*The number of city codes has to be equal to the number of names of these locations!*

key: the API-key you got, after registering on worldweatheronline.com
image: wether to show icons or not ("1" or "0"), default is "1"
sprite_image: wether to use css_sprite file on your server ("1") or to retrieve the icons from worldweatheronline ("0"), default is "1"
wind_unit: show wind speed in km/h or mph, choose between "k" or "m", default is "k"
temperature_unit: use Fahrenheit or Celsius scale, "C" or "F", default is "C"
class: table class, default is "weather"
row_class: row class, default is "weather-row"
place_class: td class for the city, default is "weather"
temperature_class: td class for the temperature, default is "weather"
icon_class: td class for the icons or the sprite_image, default is "weather"
language: what language to use for title texts? default is English "en" other language codes you can find "*here*":http://www.worldweatheronline.com/api/docs/multilingual.aspx
wait: time to wait between requests for different cities in micro seconds (one millionth of a second). Default is 500000 micro seconds.

Released under the General Public License 2.0

The Iconset has another license! Look "*here*":http://www.worldweatheronline.com/free-weather-feed.aspx

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

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


function wcz_weather($atts) {
  
  extract(lAtts(array(
    'codes'=>'',
    'names'=>'',
    'key'=>'',
    'forecast'=>'0',
    'image'=>'1',
    'sprite_image'=>'1',
    'wind_unit'=>'k',
    'temperature_unit'=>'c',
    'place_class'=>'weather',
    'temperature_class'=>'weather',
    'icon_class'=>'weather-sprite',
    'row_class'=>'weather',
    'class'=>'weather',
    'language'=>'en',
    'wait'=>'500000',
    'debug'=>'0'
  ),$atts));
  
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
    $Out[$location] = '<tr class="'. $row_class .'"><td class="'. $place_class .'" title="'. $rowtext .' '. $place .'">'. $place .'</td>'. $Out[$location] .'</tr>';
  }
  
  
  $all='<table class="'. $class .'">';
  foreach ($Out as $a) {
    $all.=$a;
  }
  
  $all=$all.'</tr></table>'.$datatext.' <a href="http://www.worldweatheronline.com/" title="Free Weather API" target="_blank">World Weather Online</a>';
  return($all);
}



# --- END PLUGIN CODE ---

?>
