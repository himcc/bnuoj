<?php include("header.php"); ?>
<center>
<?php

function mkdirs($path, $mode = 0755) //creates directory tree recursively 
{ 
    $dirs = explode('/',$path); 
    $pos = strrpos($path, "."); 
    if ($pos === false) { // note: three equal signs 
    // not found, means path ends in a dir not file 
        $subamount=0; 
    } 
    else { 
        $subamount=1; 
    } 
    for ($c=0;$c < count($dirs) - $subamount; $c++) { 
        $thispath=""; 
        for ($cc=0; $cc <= $c; $cc++) { 
            $thispath.=$dirs[$cc].'/'; 
        } 
        if (!file_exists($thispath)) { 
            //print "$thispath<br>"; 
            mkdir($thispath,$mode); 
        } 
    } 
}


    function pimage($ori) {
        $last=0;
        //echo $ori;
        while (stripos($ori,"<img",$last)!==false) {
            $now=stripos($ori,"<img",$last);
            $beg=$now;
            //echo $now;
            $now=$now+2;
            $now=stripos($ori,"src",$now);
            //echo $now;
            $now+=strlen("src");
            while ($ori[$now]!='=') $now++;
            $now++;
            while ($ori[$now]==' '||$ori[$now]=='"'||$ori[$now]=='\'') $now++;
            //echo $now." ".$ori[$now];
            $last=$now;
            while ($ori[$last]!=' '&&$ori[$last]!='>'&&$ori[$last]!='"'&&$ori[$last]!='\'') $last++;
//            $last--;
//            if(stripos($ori,"\"",$now)===false) {
//                $last=stripos($ori,">",$now);
//            }
//            else $last=stripos($ori,"\"",$now);
//            echo $ori[$last];
            $url=substr($ori,$now,$last-$now);
            

            $name='images/nbut/'.basename($url);
            if (strpos($url,"http://")===false) {
                if ($url[0]=='/') $url="http://cdn.ac.nbutoj.com".$url;
                else $url="http://cdn.ac.nbutoj.com/".$url;
            }
            //echo $name;
            //die();
            mkdirs("/var/www/contest/".$name);
            $fp = fopen("/var/www/contest/".$name, "wb");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //curl_setopt($ch, CURLOPT_FILE, $fp);
            $content = curl_exec($ch); 
            curl_close($ch); 
            fwrite($fp, $content);
            fclose($fp);
            $ori=substr($ori,0,$now).$name.substr($ori,$last);
            //echo $url;
            //die();
            //file_put_contents("/var/www/contest/".$url, $content);

            //$fp=fopen("/var/www/contest/".$url,"w+");
            //fclose($fp);
            $last=$now;
        }
        return $ori;
    }



    $from=$_GET['from'];
    $to=$_GET['to'];
    if ($to-$from>100) {
        echo "Too many!\n".
        "</center>".
        "<br>";
        include("footer.php");
        die();
    }
    if ($to==""||$from=="") {
        echo "Invalid!\n".
        "</center>".
        "<br>";
        include("footer.php");
        die();
    }

    for ($pid=$from;$pid<=$to;$pid++) {
        $res=mysql_query("select pid from problem where vname like 'NBUT' and vid like '$pid'");
        list($num)=mysql_fetch_array($res);
        if ($num) {
            echo "NBUT $pid Already Exist, pid:$num.<br>\n";
            //continue;
        }
        $url="http://ac.nbutoj.com/Problem/view.xhtml?id=$pid";
        $content=file_get_contents($url);
        //echo htmlspecialchars($content);die();
        if (strpos($content,"<h3>[] </h3>")===false) {
            $chr="<div id=\"big_title\">";
            $pos1=stripos($content,$chr)+strlen($chr);
            $chr="<h3>";
            $pos1=stripos($content,$chr,$pos1)+strlen($chr);
            $pos2=stripos($content,"</h3>",$pos1);
            $pos1+=6;
            $title=trim(substr($content,$pos1,$pos2-$pos1));
//            echo "Title: ".$title."<br>\n";die();

            $chr="时间限制: ";
            $pos1=stripos($content,$chr,$pos2)+strlen($chr);
            $pos2=stripos($content,"ms",$pos1);
            $time_limit=trim(substr($content,$pos1,$pos2-$pos1));
//            echo "Time Limit: ".$time_limit."<br>\n";die();

            /*$chr="><b>Case Time Limit:</b> ";
            if (strpos($content,$chr,$pos2)!==false) { 
                $pos1=strpos($content,$chr,$pos2)+strlen($chr);
                $pos2=strpos($content,"MS</td>",$pos1);
                $case_limit=substr($content,$pos1,$pos2-$pos1);
            }
            else*/ $case_limit=$time_limit;
//            echo "Case Time Limit: ".$case_limit."<br>\n";die();

            $chr="内存限制:";
            $pos1=stripos($content,$chr,$pos2)+strlen($chr);
            $pos2=stripos($content,"K",$pos1);
            $mem_limit=trim(substr($content,$pos1,$pos2-$pos1));
//            echo "Memory Limit: ".$mem_limit."<br>\n";die();

            $chr="<li class=\"contents\" id=\"description\">";
            $pos1=stripos($content,$chr,$pos2)+strlen($chr);
            $pos2=stripos($content,"<li class=\"titles\"",$pos1);
            $pos2=strripos($content,"</li>",$pos2-strlen($content));
            $desc=substr($content,$pos1,$pos2-$pos1);
//            echo "Desc: ".htmlspecialchars($desc)."<br>\n";die();
            $desc=pimage($desc);
//            echo "Desc: ".htmlspecialchars($desc)."<br>\n";die();

            $chr="<li class=\"contents\" id=\"input\">";
            $pos1=stripos($content,$chr,$pos2)+strlen($chr);
            $pos2=stripos($content,"<li class=\"titles\"",$pos1);
            $pos2=strripos($content,"</li>",$pos2-strlen($content));
            $inp=substr($content,$pos1,$pos2-$pos1);
//            echo "Input: ".htmlspecialchars($inp)."<br>\n";
            $inp=pimage($inp);
//            echo "Input: ".htmlspecialchars($inp)."<br>\n";

            $chr="<li class=\"contents\" id=\"output\">";
            $pos1=stripos($content,$chr,$pos2)+strlen($chr);
            $pos2=stripos($content,"<li class=\"titles\"",$pos1);
            $pos2=strripos($content,"</li>",$pos2-strlen($content));
            $oup=substr($content,$pos1,$pos2-$pos1);
            //echo "Output: ".htmlspecialchars($oup)."<br>\n";
            $oup=pimage($oup);
//            echo "Output: ".htmlspecialchars($oup)."<br>\n";


            $chr="<pre>";
            $pos1=strpos($content,$chr,$pos1)+strlen($chr);
            $pos2=strpos($content,"</pre>",$pos1);
            $sin=substr($content,$pos1,$pos2-$pos1);
            //echo "Output: ".htmlspecialchars($oup)."<br>\n";
//            echo "Sample In: ".htmlspecialchars($sin)."<br>\n";

            $chr="<pre>";
            $pos1=strpos($content,$chr,$pos1)+strlen($chr);
            $pos2=strpos($content,"</pre>",$pos1);
            $sout=substr($content,$pos1,$pos2-$pos1);
            //echo "Output: ".htmlspecialchars($oup)."<br>\n";
//            echo "Sample Out: <pre>".$sout."</pre><br>\n";

            $chr="<li class=\"contents\" id=\"hint\">";
            if (strpos($content,$chr,$pos2)!==false) {
                $chr="<pre>";
                $pos1=strpos($content,$chr,$pos1)+strlen($chr);
                $pos2=strpos($content,"</pre>",$pos1);
                $hint=substr($content,$pos1,$pos2-$pos1);
                if ($hint=="无") $hint="";
            }
            else $hint="";
            $hint=pimage($hint);
//            echo "Hint: ".htmlspecialchars($hint)."<br>\n";

            $chr="<li class=\"contents\" id=\"source\">";
            if (strpos($content,$chr,$pos2)!==false) {
                $chr="<pre>";
                $pos1=strpos($content,$chr,$pos1)+strlen($chr);
                $pos2=strpos($content,"</pre>",$pos1);
                $source=trim(strip_tags(substr($content,$pos1,$pos2-$pos1)));
                if ($source=="本站或者转载") $source="";
            }
            else $source="";
            //echo "Output: ".htmlspecialchars($oup)."<br>\n";
//            echo "Source: <pre>".$source."</pre><br>\n";
            
            /*if (strpos($content,"<td style=\"font-weight:bold; color:red;\">Special Judge</td>",$pos2)!==false) $spj=1;
        else $spj=0;*/

if ($num=="") $sql_add_pro = "insert into problem (title,description,input,output,sample_in,sample_out,hint,source,hide,memory_limit,time_limit,special_judge_status,case_time_limit,basic_solver_value,number_of_testcase,isvirtual,vname,vid) values ('".addslashes($title)."','".addslashes($desc)."','".addslashes($inp)."','".addslashes($oup)."','".addslashes($sin)."','".addslashes($sout)."','".addslashes($hint)."','".addslashes($source)."','0','$mem_limit','$time_limit','$spj','$case_limit','0','0',1,'NBUT','$pid')";
else $sql_add_pro = "update problem set title='".addslashes($title)."',description='".addslashes($desc)."',input='".addslashes($inp)."',output='".addslashes($oup)."',sample_in='".addslashes($sin)."',sample_out='".addslashes($sout)."',hint='".addslashes($hint)."',source='".addslashes($source)."',hide='0',memory_limit='$mem_limit',time_limit='$time_limit',special_judge_status='$spj',case_time_limit='$case_limit',vname='NBUT',vid='$pid' where pid= $num";

            //$search  = array('\n', '\t', '\r');
            //$replace = array('\\n', '\\t', '\\r');
            //$sql_add_pro=str_replace($search, $replace, $sql_add_pro);
//            echo htmlspecialchars($sql_add_pro);
            $que_in = mysql_query($sql_add_pro);
            if($que_in){
                list($currpid)=mysql_fetch_array(mysql_query("select max(pid) from problem"));
                if ($num) echo "NBUT $pid has been recrawled as pid:$num<br>\n";
                else echo "NBUT $pid has been added as pid:$currpid<br>\n";
            }


        }
        else {
            echo "No Such Problem Called NBUT $pid.<br>\n";
        }
    }

?>
</center>
<br>
<?php include("footer.php"); ?>

