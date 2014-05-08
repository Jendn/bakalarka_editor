<?php
    
function draggItems($xml) {
     $dragArr = "";
     $y=0;
     foreach($xml->xpath('//Format') as $format)  {
     $dragArr[$y] = $format->Name;
     $y++; 
                  }
     return $dragArr;
     }
function process($pravidlaXML, $metaXML) {
      $text = "";
      $spojka = "";
      foreach ($pravidlaXML->AssociationRule as $ar) {
              
               foreach ($ar->Antecedent as $ante) {
                     $text .= "IF \n";
                  foreach($ante->Cedent as $cedent) {
                     $text .= processCedent($metaXML, $cedent, "or");
                      
                  }
               }
                $text .= "\n";
                $text .= "THEN \n";
                foreach($ar->Consequent as $conse) {
                   foreach($conse->Cedent as $cedent) {
                       $text .= processCedent($metaXML, $cedent, "or");
                   }
                }                  
               }
                return $text;
}
/**
 * @param $metaXML
 * @param $cedent
 * @param $text
 * @return array
 */
function processCedent($metaXML, $cedent, $puvSpoj)
{
    $textCed = "";
    $neg = 0;
    $jePar = 0;
    $attrs = $cedent->attributes();
    if ($attrs["connective"] == "Disjunction") {
        $spojka = "or";
    } else if ($attrs["connective"] == "Conjunction") {
        $spojka = "and";
    } else {
        $spojka = $puvSpoj;
        $neg = 1;
    }
   // echo $cedent->asXML();
    foreach($cedent->Cedent as $cedChild) {
        $textCed .= processCedent($metaXML, $cedChild, $spojka);
        $jePar = 1;
        $textCed .= " $spojka ";
   //     return $textCed;
    }
    if($cedent->Cedent) {
        $textCed = substr($textCed, 0, -(strlen($spojka)+2));
    }
      
     if($jePar != 1) {
    if ($neg == 1) {
        $textCed .= "not(";
        }
        foreach ($cedent->Attribute as $attr) {
            $attAttr = $attr->attributes();
            $attrAttr = $attAttr["format"];
            foreach ($metaXML->xpath('//Format') as $format) {
                $attFormat = $format->attributes();
                $formatAttr = $attFormat["id"];
                if (!strcmp($attrAttr, $formatAttr)) {
                    $attributeName = $format->Name;
                    $textCed .= '"'. $attributeName .'" ';
                }
            }
            foreach ($attr->Category as $cat) {
                $attCat = $cat->attributes();
                $catUri = $attCat["id"];
                // echo $attrAttr;
                $pomocPrubeh = 0;
                foreach ($metaXML->xpath("//Format[@id = '$attrAttr']//NominalBin") as $bin) {
                    $attBin = $bin->attributes();
                    $binUri = $attBin["id"];
                    if ((string)$binUri == (string)$catUri) {
                        $textCed .= processBin($bin);
                        $pomocPrubeh = 1;break;
                    }
                }
                if($pomocPrubeh != 1) {
                foreach ($metaXML->xpath("//Format[@id = '$attrAttr']//IntervalBin") as $bin) {
                    $attBin = $bin->attributes();
                    $binUri = $attBin["id"];
                    if ((string)$binUri == (string)$catUri) {
                        $textCed .= processBin($bin);
                    }
            }
                }
                }
            $textCed = substr($textCed, 0, -3);
            $textCed .= " $spojka ";
        }
        $lengthS = strlen($spojka) + 2;
        $textCed = substr($textCed, 0, -$lengthS);
        }
        if ($neg == 1) {
          $textCed .= ")";
        }
        return $textCed;
    }
 //   return $textCed;
/**
 * @param $bin
 * @param $text
 * @return array
 */
function processBin($bin)
{
    if (isset($bin->Value)) {
        $attributeValue = $bin->Value;
        $textBin = 'equals "'. $attributeValue.'" or';
        return $textBin;
    } else {
        $interval = $bin->Interval->attributes();
        $spodniKraj = $interval["leftMargin"];
        $horniKraj = $interval["rightMargin"];
        switch ($interval["closure"]) {
            case "closedClosed":
                $textBin = 'equals or is higher than "' . $spodniKraj . '" and equals or is lower than "' . $horniKraj . '" or ';
                break;
            case "openClosed":
                $textBin = 'is higher than "' . $spodniKraj . '" and equals or is lower than "' . $horniKraj . '" or ';
                break;
            case "closedOpen":
                $textBin = 'equals or is higher than "' . $spodniKraj . '" and is lower than "' . $horniKraj . '" or ';
                break;
            case "openOpen":
                $textBin = 'is higher than "' . $spodniKraj . '" and is lower than "' . $horniKraj . '" or ';
        }
                return $textBin;
        }
    }
    
    function sestavXML($formats, $text) {
      $cleanArr = clean($text);
      $cleanArr = connect($cleanArr);
      $vysledek = model($cleanArr, $formats);
      return $vysledek;
    }
    
    function model($cleanArr, $formats) {
       $expects = array();
       $expects[] = "IF";
       $x = 0;
       $xml = new SimpleXMLElement('<AssociationRule/>');
       for($x; $x <= count($cleanArr); $x++) {
        foreach($expects as $expect) {
          if($cleanArr[$x] == $expect) {
            $control = true;
            break;
          }  }
        /*  if($expects[0] == "Anything")
          $control = true;
          if($control != true) {
            return "FAILED TO PROCESS, EXPECTED VALUES ".print_r($expects);
          }       */
          switch($cleanArr[$x]) {
                case "IF": $antecedent = $xml->addChild("Antecedent"); 
                           $last = $antecedent;
                           $expects = $formats;
                           break;
                case "not(": $cedent = $last->addChild("Cedent");
                             $cedent->addAttribute('connective', "Negation");
                            // $last = $cedent;
                             $expects = $formats;
                             $state = "Ced";
                             break;
                case "equals": $expects = ("Anything");
                case "equals or is higher than": $category = $attribute->addChild("Category");
                                                 $closure = "closed";
                                                 $state = "INTL";
                                                 break;                
                case "equals or is lower than":  $closure .= "Closed";
                                                 $state = "INTR";
                                                 break;
                case "is higher than":  $category = $attribute->addChild("Category");
                                                 $closure = "open";
                                                 $state = "INTL";
                                                 break;
                case "is lower than":            $closure .= "Open";
                                                 $state = "INTR";
                                                 break;
                case "and": if ($state=="INTL");
                               
                                break;              ;
                case "or":     break;
                default: if($state == "Ced") {
                $attribute = $cedent->addChild("Attribute");
                $attribute->addAttribute("format", "$cleanArr[$x]");
                } else if($state == "INTL") {
                   $category->addAttribute("leftMargin", "$cleanArr[$x]");  
                } else if($state == "INTR") {
                   $category->addAttribute("rightMargin", "$cleanArr[$x]");
                   $category->addAttribute("closure", "$closure");
                   $state = "";
                   return $xml;  
                }                
                else {
                  $category = $last->addChild("Category");
                  $category->addAttribute("id", "");
                } break;
          
          }
       }
    }
    
    function clean($text) {
      $arrOrig = preg_split('/[\s]+/' ,$text);
      $x = 0;
      $zacatek = 0;
      foreach($arrOrig as $word) {
        
        if(startsWith($word,"not(")) {
           $clean[$x] = "not(";
           $x++;
           $clean[$x] = substr($word, 4);
           $word = $clean[$x];
        } 
        if(startsWith($word, '"') && !(endsWith($word, ")"))) {
         if ($zacatek != 1) {
          $zacatek = 1;
          
          if(endsWith($word, '"')) {
            $zacatek = 0;
            $pomocnaClean = substr($word, 1);
            $clean[$x] = substr($pomocnaClean, 0, -1);
          } else {
          $clean[$x] = substr($word, 1);  }
          } else {
            return "Chyba, zacatek uvozovek pred ukoncenim predchoziho retezce $x";
          }
        }
       elseif(endsWith($word, '"')) {
          if ($zacatek != 0) {
          $zacatek = 0;
          $x--;
          $clean[$x] .= " ";
          $clean[$x] .= substr($word, 0, -1);
          } else {
            return "Chyba, konec uvozovek pred zacatkem $x";
          }
       } elseif(endsWith($word, ')')) {
          $word = substr($word, 0, -1);
          if(endsWith($word, '"') && !(startsWith($word, '"'))) {
            if($zacatek == 1) {
               $x--;
               $clean[$x] .= substr($word, 0, -1);
               $x++;
               $clean[$x] = ")";
            } else {
               return "Chyba, konec uvozovek pred zacatkem $x";
            }
            
          }
          if(endsWith($word, '"') && startsWith($word, '"')) {
            
            $pomocnaClean = substr($word, 1);
            $clean[$x] = substr($pomocnaClean, 0, -1);
            $x++;
            $clean[$x] = ")";
          }          
       }
       elseif($zacatek == 1) {
        $x--;
        $clean[$x] .= $word;
       } else {
         $clean[$x] = $word;
       }
       $x++;
      }
      return $clean;
      
    }
    
    function connect($toClean) {
      $clean = array();
      $y = 0;
       for($x = 0; $x < count($toClean); $x++) {
          if($toClean[$x] == "equals" && $toClean[$x+1] == "or" && $toClean[$x+2] == "is" && $toClean[$x+3] == "higher" && $toClean[$x+4] == "than") {
             $clean[$y] = "equals or is higher than";
             $x = $x+4; 
          } else if($toClean[$x] == "equals" && $toClean[$x+1] == "or" && $toClean[$x+2] == "is" && $toClean[$x+3] == "lower" && $toClean[$x+4] == "than") {
             $clean[$y] = "equals or is lower than";
             $x = $x+4; 
          } else if($toClean[$x] == "is" && $toClean[$x+1] == "higher" && $toClean[$x+2] == "than") {
             $clean[$y] = "is higher than";
             $x = $x+2;
             } else if($toClean[$x] == "is" && $toClean[$x+1] == "lower" && $toClean[$x+2] == "than") {
             $clean[$y] = "is lower than";
             $x = $x+2;
             } else {
             $clean[$y] = $toClean[$x];
             }
       $y++;
       }
    
    return $clean;
    }
    
    function startsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}
    function endsWith($haystack, $needle)
{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}
?>