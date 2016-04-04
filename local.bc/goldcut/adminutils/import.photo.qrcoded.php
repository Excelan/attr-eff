<?php

if (ADMIN_AREA !== true) {
    require dirname(__FILE__) . '/../boot.php';
}

die("Not active. Use /qrbatch/");

set_time_limit(0);
ob_implicit_flush();

$sc = new GcSocketClient(COMMANDLETS_HOST, COMMANDLETS_PORT);

function qr_process($ns, $sc)
{

    if ($ns) $ns = '/'.$ns;
    $dirin = realpath(__DIR__."/../../BATCH/in{$ns}");
    $dirout = realpath(__DIR__."/../../BATCH/out{$ns}");
    $imgs = Utils::list_dir_images($dirin, true);
    printH(count($imgs));

    $id = 0;

    $product = null;

    $imgsk = array();
    foreach ($imgs as $img) {
        $exif = ImageMeta::getExif($img);
        $taken = (int) $exif['taken'];
        $imgsk[$taken] = $img;
    }
    ksort($imgsk);
    $i=0;
    foreach ($imgsk as $taken => $img)
    {
        $i++;
        $in = json_encode(array('seq'=>$i,'gate'=>'mediacenter/qrcodetedector','action'=>'read','filepath' => $img));
        $out = $sc->sendAndRecieve($in);
        if (FORCE32BIT)
        {
            $testid = (int) $out['data']['code'];
            if ($testid > 4294967295)
                $testid = (int) substr($out['data']['code'],0,9);
            $qrcode = $testid;
        }
        else {
            $qrcode = (int) $out['data']['code']; // new qrcode or 0 if no in image
        }

        if ($qrcode > 0) $currentproductid = $qrcode;
        if ($currentproductid == 0) throw new Exception("NO QR CODE FIRST");

        $fe = Utils::filename_extension($img);

        $skipControlFrame = false;
        if ($qrcode > 0)
        {
            printH($qrcode);
            // CHECK FOR QRCODE ALREADY USED
            $m = new Message();
            $m->action = 'load';
            $m->urn = 'urn:product';
            $m->id = $qrcode;
            $existsproduct = $m->deliver();
            //println($existsproduct,1,TERM_RED);
            $comment = null;

            if (count($existsproduct)) {
                println("RETURN TO CODE ".$qrcode,1,TERM_VIOLET);
                $product = $existsproduct;
                $skipControlFrame = true;
            }
            else
            {
                $m = new Message();
                $m->action = 'create';
                $m->urn = 'urn:product';
                $m->id = $qrcode;
                if ($comment) $m->productDesc = $comment;
                try {
                    //println($m,1,TERM_GRAY);
                    $product = $m->deliver();
                    //println($product, 1, TERM_GREEN);
                }
                catch (Exception $e) {
                    println($e, 1, TERM_RED);
                }
            }
            /*
            if (count($existsproduct)) {
                $comment = 'Fallback from printed and used '.$qrcode;
                $qrcode = mt_rand(10000000, 99999999); // 8 digits
                $currentproductid = $qrcode;
                println("DUPLICATE CODE ".$qrcode,1,TERM_RED);
                println("CODE CUR ".$qrcode,1,TERM_GRAY);
            }
            else {
                //println("NO DUP",1,TERM_GREEN);
            }

            $m = new Message();
            $m->action = 'create';
            $m->urn = 'urn:product';
            $m->id = $qrcode;
            if ($comment) $m->productDesc = $comment;
            try {
                //println($m,1,TERM_GRAY);
                $product = $m->deliver();
                //println($product, 1, TERM_GREEN);
            }
            catch (Exception $e) {
                println($e, 1, TERM_RED);
            }
            */
        }

        print "<p><a href='/goldcut/admin/?urn={$product->urn}&action=edit&lang=ru'>edit</a></p>";

        $filename = $fe['name'].'.'.$fe['extension'];
        $infile = "{$dirin}/{$filename}";
        $outfile = "{$dirout}/{$filename}";

        if ($skipControlFrame == true) {
            println("SKIP {$taken} " . $img, 1, TERM_VIOLET);
            rename($infile, $outfile);
            continue;
        }

            $m = new Message();
            $m->action = 'create';
            $m->file = $img;
            $m->uri = $filename;
            $m->urn = 'urn:productphoto';
            $m->needprocess = true;
            $m->product = 'urn:product-' . $currentproductid;
            if ($qrcode > 0) $m->controlframe = true;
            try {
                //println($m,1,TERM_GRAY);
                $r = $m->deliver();
                println("OK {$taken} " . $img, 1, TERM_GREEN);
                rename($infile, $outfile);
            } catch (Exception $e) {
                println("ERROR: Не могу создать фото " . $img, 1, TERM_RED);
                println($m);
                print $e;
            }

        //println($r);
    }

}

$ns = null;
qr_process($ns, $sc);

$sc->sendAndRecieve('close');

?>