<?php
function public_url($url)
{
  $base = url('/');

  return $base . '/public/' . $url;
}


function usertype($type)
{
  switch ($type) {
    case '1':
      return "सुपर एडमिन";
    case '2':
      return "एडमिन";
    case '3':
      return "अधिकारी";
    case '4':
      return "प्रयोगकर्ता";

    default:
      return "";
  }
}

function yesno($type)
{
  switch ($type) {
    case '0':
      return "छैन";
    case '1':
      return "छ";

    default:
      return "";
  }
}

function yes_no($type)
{
  switch ($type) {
    case '0':
      return "होइन";
    case '1':
      return "हो";

    default:
      return "";
  }
}

function program_type($type)
{
  switch ($type) {
    case '1':
      return "अध्ययन";
    case '2':
      return "तालिम";

    default:
      return "";
  }
}

function isAllowd($type)
{
  if ($type == '1')
    echo "<i class='fa fa-check'></i>";
  else
    echo "";
}

function hashtag($id)
{
  return substr(sha1($id), 3, 21);
}

function hashtag_field($id)
{
  return "<input type='hidden' name='hashtag' value='" . hashtag($id) . "'>";
}

function s_tk()
{
  return "<input type='hidden' name='s_tk' value='" . csrf_token() . "'>";
}

function sanitize($string)
{
  $pdata = entity_convert($string);
  return UTF8toEng($pdata);
}

function sanitizeSearch($data)
{
  $pdata = entity_convert($data);
  return $pdata;
}

function entity_convert($string)
{
  $pdata = htmlentities($string, ENT_QUOTES, "UTF-8");
  return addslashes($pdata);
}

function decode($data)
{
  return html_entity_decode($data);
}

function UTF8toEng($string)
{
  $patterns[0] = '0';
  $patterns[1] = '1';
  $patterns[2] = '2';
  $patterns[3] = '3';
  $patterns[4] = '4';
  $patterns[5] = '5';
  $patterns[6] = '6';
  $patterns[7] = '7';
  $patterns[8] = '8';
  $patterns[9] = '9';
  $replacements[0] = '/०/';
  $replacements[1] = '/१/';
  $replacements[2] = '/२/';
  $replacements[3] = '/३/';
  $replacements[4] = '/४/';
  $replacements[5] = '/५/';
  $replacements[6] = '/६/';
  $replacements[7] = '/७/';
  $replacements[8] = '/८/';
  $replacements[9] = '/९/';
  return preg_replace($replacements, $patterns, $string);
}

function EngToUTF8($string)
{
  $num = array(
    "-" => "-",
    "0" => "०",
    "1" => "१",
    "2" => "२",
    "3" => "३",
    "4" => "४",
    "5" => "५",
    "6" => "६",
    "7" => "७",
    "8" => "८",
    "9" => "९"
  );
  return strtr($string, $num); //corrected 
}

function RandomVal($val = 8)
{
  $characters = '0123456789abcdefghijk14725836907456983210lmnopqrstuvwxyz9876543210zyxwvutsrqponml3654789210kjihgfedcba';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $val; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}

function RandomNum($val = 8)
{
  $characters = '0123456789987654321014702583699512365478975623148967';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $val; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}

function noData($colspan)
{
  echo "<tr><td colspan='$colspan'>Recors are empty.</td></tr>";
}

function  checkHash($id, $hash)
{
  if (hashtag($id) === $hash)
    return true;

  return false;
}

function make_route($route, $id = null)
{
  if ($id) {
    return route($route, [$id, hashtag($id)]);
  }
  return route($route);
}

function participationType($type)
{
  switch ($type) {
    case '0':
      return "पदाधिकारी";
    case '1':
      return "आमन्त्रित कर्मचारी";

    default:
      return "";
  }
}

function trainingType($type)
{
  switch ($type) {
    case '0':
      return "Online";
    case '1':
      return "Physical";

    default:
      return "";
  }
}

function nomination($type)
{
  switch ($type) {
    case '0':
      return "मनोनयन नभएको";
    case '1':
      return "मनोनयन भएको";
    case '2':
      return "मनोनयन भएको";
    case '3':
      return "मनोनयन भएको (मुख्य)";
    case '4':
      return "मनोनयन भएको (वैकल्पिक)";

    default:
      return "";
  }
}

function selected($type)
{
  switch ($type) {
    case '0':
      return "छनोट नभएको";
    case '1':
      return "छनोट भएको";
    case '2':
      return "छनोट भएको";
    case '3':
      return "छनोट भएको (मुख्य)";
    case '4':
      return "छनोट भएको (वैकल्पिक)";

    default:
      return "";
  }
}

function samayojan($type)
{
  switch ($type) {
    case '1':
      return "संघ";
    case '2':
      return "प्रदेश";
    case '3':
      return "स्थानीय तह";

    default:
      return "";
  }
}


function get_updated_data($oldData, $editData)
{
  $updatedData = [];
  foreach ($editData->getChanges() as $key => $ufield) {
    $updatedData[$key] = $oldData[$key];
  }

  unset($updatedData['updated_at']);
  // dd($updatedData);

  if (count($updatedData))
    return (json_encode($updatedData, JSON_UNESCAPED_UNICODE));

  return false;
}
