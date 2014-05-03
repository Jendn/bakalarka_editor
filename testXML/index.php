<?php require "funkce.php" ?>

<!DOCTYPE html       PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">                 
  <head>                           
    <meta http-equiv="content-type" content="text/html; charset=utf-8">                           
    <link rel="stylesheet" type="text/css" media="all" href="styl.css"> 
    <script language="JavaScript" type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
<script language="JavaScript" type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.5.2/jquery-ui.min.js"></script>
<script language="JavaScript" type="text/javascript" src="js/drag.js"></script>                          
    <title>Editor                         
    </title>                 
  </head>                 
  <body>                           
    <div id="obal">                                       
      <div class="head">                                            
      </div>                                       
      <div class="content">                                                   
        <div class="lefty">                                                               
          <div class="texta">           <h2>Rule editor</h2>                                                                            
            <form action="" method="post">                                                                                        
              <div>                                                                                                
                <p>                                                                                              
                  <input type="radio" name="stejne" value="prvni">SBVR<br />                                                                                                
                  <input type="radio" name="stejne" value="druha">DRL                                                                                                  
                  <input type="submit" value="Proved" id="sub">                                                                                       
                </p>                                                                          
              </div>                 
<textarea autofocus="autofocus" cols="75" rows="20" id="drop"><?php
               $pravidlaXML = simplexml_load_file("testXML/data2.xml");
               $metaXML = simplexml_load_file("testXML/metaatributes.xml");
               echo process($pravidlaXML, $metaXML);
               
               ?></textarea>                                                                                             
            </form>                                                               
          </div>                                                   
        </div>                                                   
        <div class="righty">                                                               
          <div class="att">          <h2>Alternatives</h2>              
            <div style="width:230px; height:330px;">
                <?php     $nazvy = draggItems($metaXML);
                          $cisloNazev = 0; 
                          foreach($nazvy as $nazev) {
                            echo '<p class="drag">"'. $nazev[$cisloNazev]. '"</p>';
                          }
                        
                  ?>
             </div>                                                                 
          </div>                                                   
        </div>                                       
      </div>                                                                           
      <div class="foot">      Ronovský jan, v 1.0, návrh grafiky editoru korespondující s grafikou izi mineru -                                              
        <a href="http://sewebar-dev.lmcloud.vse.cz/izi-miner/">Sewebar, VŠE</a>                                       
      </div>                             
    </div>                 
  </body>
</html>