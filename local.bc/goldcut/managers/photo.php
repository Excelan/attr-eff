<?php

// TODO nowhere use of $host_urn
// TODO width x height in db - resize size. те при создании картинка получает размеры по умолч, а меняем в базе - ресайзится.

/**
    compose frames. 2/3/4/6/freeform/circle/bg

    no base file, photo
    process policy (max mp, formats, color rgb, cmyk)
    store policy (store, max)

    struct: title, original url,
        array[] f:thumbnails {w, h, base64}
        image url(if size > 20kb) {w, h, url(local or cdn)} || base64inline {w, h, base64}

    input base64 / url(scheme file, http)
    store original - local, cloud
    metadata from exif - base64: pre in js / url: after upload

    after effects - color overlay, bw (on image or thumbnails)

    postprocess - png optimize pngcrush -rem alla -reduce -brute image.png result.png
    jpg opt - jpegtran -copy none -optimize -perfect src.jpg dest.jpg

    crop frame over original (editable crop)
    microavatar based on avatar (square face oriented) config? or after.create auto build?

    ? 200:max(std with margins),200:min(facebook squares)

    size paradigms:
        HE horizontaly equal  (!portrait is hor pri not port oriented, landscape similar)
            +crop exact height
        VE verticaly equal
            +crop exact width. by vertical limit or strict size (portrait oriented similar but concept is in )

        TS tall stripe pinterest - equal width priority, no vertical limit,
            crop +square
        WS widestripe stripe - site headers
        C container - maximum any dimension. vert/hor equal role. for screenshots
        S square

    Size Paradigm - any image size and any thumb/preview sizes are proportionaly
    paradigm shift (from image sizes to thumbnails) - HE,VE can be Self or S, S can be Self, TS, WS can be Self, C can be Self or S
    all can have square frame(non crop!) with pivot in center, top or by detected face

    <=110px. same frame with thumbnail, but smaller size
    micro - base64 db inlined
    <400px, >110px. frame position
    preview - non db stored or another table, cached

    >400px, >32kb.
    image - full size, binary file, local or cloud url

    ? image frame from orig? or only thumbnails are framed. we can cut(non frame) originals.
    store original if only thumb size used. dont store original if image size used and thumb framed from original

    frameset can act as original

    photo entity f:reframe {x,y,zoom}

    <imagesettings>
        <params>
            <iparam name="detectfaces" value="no" />
            <iparam name="detectcolors" value="no" />
            <iparam name="hashbhsv" value="no" />
            <iparam name="trim" value="no" />
        </params>
        <policy>
            <ipolicy name="storeoriginal" value="no" />
            <ipolicy name="originalmaximummp" value="8" />
            <ipolicy name="resizebiggeroriginals" value="yes" />
            <ipolicy name="allowedcolorspaces" value="rgb|cmyk|gs|etc" />
            <ipolicy name="allowedmediatypes" value="jpeg,png" />
            <ipolicy name="smaller" value="asis|pad|decline|stretch" />
        </policy>
        <aftereffects>
            <effect name="bw" />
            <effect name="tint" value="#FF0000" weight="70" />
        </aftereffects>

        <image paradigm="HE" hd="no" watermark="no" verticalmin="0.5" verticalmax="0.75">
            <size horizontal="400" />
        </image>
        <previews paradigm="S" watermark="no" hd="no" reframe="yes">
            <size eachside="200" name="preview" />
            <size eachside="100" name="thumbnail" store="base64" />
        </previews>
    </imagesettings>


    <image paradigm="HE" hd="no" watermark="no" verticalfixed="0.75">
        <size horizontal="400" />
    </image>
    <previews paradigm="HE" watermark="no" hd="no" reframe="yes">
        <size horizontal="100" name="thumbnail" />
    </previews>

    <image paradigm="VE" hd="no" watermark="no" horizontalfixed="0.7">
        <size vertical="100" />
    </image>
    <previews paradigm="S" watermark="no" hd="no" reframe="yes">
        <size eachside="50" name="thumbnail" store="base64" />
    </previews>

    <image paradigm="C" hd="no" watermark="no">
        <size largestside="1024" />
    </image>
    <previews paradigm="S" watermark="no" hd="no" reframe="yes">
        <size eachside="50" name="thumbnail" store="base64" />
    </previews>

*/
// naming - /media/E/uuid/ uri.ext (+ x2 HD)
// /thumb/E/xsizename/ uuid.ext (+ x2 HD) (old need conversion from media/E/id_xsize.ext)

// if (MEDIASERVERS > 1) $m->mediaserver = mt_rand(1, MEDIASERVERS);

/*
1 photo - many usage: css class define as background-image, background-size (ie7,8 filter, non sprites only)
filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='images/logo.gif',sizingMethod='scale');
-ms-filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='images/logo.gif',sizingMethod='scale')";
http://davidwalsh.name/demo/background-size.html
*/

/*
 * <field name="freeareas" />
        <field name="histogram64" />
        <field name="binaryimage" />
        <field name="color" />
        <field name="color2" />
 */

class photo extends EManager
{
    protected function config()
    {
        $this->behaviors[] = 'general_crud';
        $this->behaviors[] = 'general_multylang';
        $this->behaviors[] = 'general_list';
        $this->behaviors[] = 'general_ordered';
    }

    /**
    get file
    get file metadata
    store original or not
    get fast thumbnail
    save db (s:needprocess, base64earlythumb)
    return + defered process

    analize image size paradigm, faces, main content center. is smaller side enough?
    update db (s:needprocess)
    Paradigm Image
        crop?
        resize
    Paradigm Previews
        crop?
        resize, resize..

    */
    public function create($m)
    {
        Log::debug($m, 'image');

        //		$message = $m;
        //$m->thumbnails = $m->base64data;
        if (!$m->id) {
            $uuid = new UUID();
        } else {
            $uuid = new UUID($m->id);
        }

        $m->id = $uuid->toInt();

        if (defined('JUSTSTORE') && JUSTSTORE === true) {
            $illu = Entity::store($m);
            Log::info('JUST STORE', 'image');
            return $illu;
        }

        // original filename
        $filename = Utils::filename_extension($m->uri); // !! need uri.ext not uri
        $uri = $filename['name'];
        $ext_lc = strtolower($filename['extension']);
        $sourceExt = $ext_lc;

        $uri = '/original/' . $uuid . "." . $ext_lc;
        $original =  BASE_DIR . $uri;
        $m->original = $uri;

        // SUPPORTED MEDIA TYPES, EXTENSIONS
        $transitionExtTable = array('jpg'=>'jpg','jpeg'=>'jpg','gif'=>'gif','tif'=>'jpg','tiff'=>'jpg','bmp'=>'jpg','png'=>'png'); // TODO simple png > jpg, png with alpha > as is
        $targetExt = $transitionExtTable[$sourceExt];
        if (!$targetExt) {
            throw new Exception("No targetExt. sourceExt: {$sourceExt}");
        }

        // if (!$original) throw new Exception("No original file for Image::processSizes");

        if ($m->file) {
            if (is_uploaded_file($m->file)) {
                move_uploaded_file($m->file, $original);
            } else {
                copy($m->file, $original);
            }
        } elseif ($m->base64data) {
            // original decode base64 data
            $filebin = base64_decode_file($m->base64data);
            $m->clear('base64data');
            save_data_as_file($original, $filebin);
        } else {
            throw new Exception('Provide .file or .base64data');
        }
        // from URL
        // TODO
        $m->filesize = filesize($original);

        $E = $m->urn->E();
        $message = $m;
        //$size = $E->mediaoptions;

        // Titling
        // TODO if meta exif provided earlier
        $title_anons = $this->optimalTitle($original, $E);
        $m->title = $title_anons['title'];
        if ($title_anons['description']) {
            $m->description = $title_anons['description'];
        }
        if ($title_anons['taken']) {
            $m->taken = $title_anons['taken'];
        }

        // Naming
        $m->uri = $this->optimalName($filename['name'], $E, $uuid);
        $m->ext = $targetExt;
        // dprintln("m->uri {$m->uri}",1,TERM_RED);

        // prefix folder
        $randPrefix = rand(1, 1000);
        $prefixFolder = $randPrefix.'/'.$uuid.'/';
        //$prefixFolderPreview = $randPrefix.'/';
        $prefixFolderPreview = $prefixFolder;
        $m->folder = $prefixFolder;

        //dprintln($m);
        // dprintln($E->imagesettings,1,TERM_GRAY);

        $originalParadigm = Image::analizeImageParadigm($original);
        //dprintln($originalParadigm,1,TERM_GRAY);

        $targetParadigm = $E->imagesettings['mainimage'];
        //dprintln($targetParadigm,1,TERM_RED);

        // list($otherSideKey, $otherSideValue, $targetParadigm['size']['width'], $targetParadigm['size']['height']) =
        Image::calcParadigmParameters($targetParadigm, $originalParadigm);
        // $targetParadigm['size']['ratio'] = $targetRatio;
        //dprintln("otherSideKey, otherSideValue, tp-size-size, targetRatio: $otherSideKey, $otherSideValue, {$targetParadigm['size']['size']}, $targetRatio");

        $parentParadigm = $targetParadigm;
        //Log::info(json_encode($targetParadigm),'imgd');
        $save_size_width = $targetParadigm['size']['width'];
        $save_size_height = $targetParadigm['size']['height'];

        $odp = $cropPossibility = Image::analizeCropPossibilitiesBetweenImageParadigms($originalParadigm, $targetParadigm);
        //dprintln($cropPossibility,1,TERM_GREEN);
        // cropPossibility get max
        $paramsCropResizeConsoleOptions = ImageExternalApp::paradigmDiffAndTargetToCropResizeCommand($cropPossibility, $targetParadigm);
        //dprintln($paramsCropResizeConsoleOptions,1,TERM_GRAY);
        //Log::info(json_encode($paramsCropResizeConsoleOptions),'imgd');

        // RESIZE IMAGE
        $store = 'media';
        $filename = $m->uri;
        $curimgPath = join('/', array( $store, $E->getFolderName(), $prefixFolder ));
        $curimgFile = $filename.'.'.$targetExt;
        $curimguri = $curimgPath . $curimgFile;
        $illu_file_uri = BASE_DIR.'/'.$curimguri;
        $IMGURI[] = $curimguri;
        mkdir(BASE_DIR.'/'.$curimgPath, octdec(FS_MODE_DIR), true);
        $imageSizeUri = $curimguri;
        $imageSizeParadigm = $targetParadigm['paradigm'];
        // dprintln($illu_file_uri,1,TERM_VIOLET);
        ImageExternalApp::processImage($original, $illu_file_uri, $paramsCropResizeConsoleOptions);

        // $sizeOfOutputImage = Image::size($illu_file_uri);
        // print "<img src='$outuri'>";
        // assertObjectsEqual($sizeOfOutputImage, array('width'=>$targetParadigm['size']['width'],'height'=>$targetParadigm['size']['height']));


        // dprintln($E->imagesettings['previews'],1,TERM_RED); // every preview image sizes paradigm
        $everyPreviewSizeParadigm = $E->imagesettings['previews']['paradigm'];
        $everyPreviewSizeHD = $E->imagesettings['previews']['hd'];

        foreach ($E->imagesettings['previews']['sizes'] as $targetParadigmName => $targetParadigmSizeParams) {
            $targetParadigm = array();
            // get value from all previews paradigm
            $targetParadigm['size'] = $targetParadigmSizeParams;
            $targetParadigm['paradigm'] = $everyPreviewSizeParadigm;
            $targetParadigm['hd'] = $everyPreviewSizeHD;

            //Log::info(json_encode($targetParadigm),'imgd');

            // if ($parentParadigm['paradigm'] == $targetParadigm['paradigm']) dprintln('IMAGE and PREVIEWS HAVE SAME PARADIGM '.$everyPreviewSizeParadigm,1,TERM_VIOLET);
            // else dprintln('IMAGE and PREVIEWS HAVE DISTINCT PARADIGMS',1,TERM_GRAY);

            // Image::calcParadigmParameters($targetParadigm, $originalParadigm);
            Image::calcParadigmParameters($targetParadigm, $parentParadigm);
            //dprintln($originalParadigm,2,TERM_GRAY);
            //dprintln($targetParadigm,2,TERM_GREEN);
            $minsideSource = min($originalParadigm['ow'], $originalParadigm['oh']);
            $minsideTarget = min($targetParadigm['size']['width'], $targetParadigm['size']['height']);

            $cropPossibility = Image::analizeCropPossibilitiesBetweenImageParadigms($originalParadigm, $targetParadigm);
            //dprintln($cropPossibility,2,TERM_GREEN);
            // cropPossibility get max
            $paramsCropResizeConsoleOptions = ImageExternalApp::paradigmDiffAndTargetToCropResizeCommand($cropPossibility, $targetParadigm);
            //dprintln($paramsCropResizeConsoleOptions,2,TERM_GRAY);

            // RESIZE IMAGE
            $store = 'thumb';
            // $filename = $uuid;
            $curimgPath = join('/', array( $store, $E->getFolderName(), $targetParadigmName, $prefixFolderPreview ));
            $curimgFile = $filename.'.'.$targetExt;
            $curimguri = $curimgPath . $curimgFile;
            $illu_file_uri = BASE_DIR.'/'.$curimguri;
            $IMGURI[] = $curimguri;
            mkdir(BASE_DIR.'/'.$curimgPath, octdec(FS_MODE_DIR), true);
            //dprintln($illu_file_uri,2,TERM_VIOLET);
            ImageExternalApp::processImage($original, $illu_file_uri, $paramsCropResizeConsoleOptions);

            // $sizeOfOutputImage = Image::size($illu_file_uri);
            // print "<img src='$outuri'> $sizeParadigm";
            // assertObjectsEqual($sizeOfOutputImage, array('width'=>$targetParadigm['size']['width'],'height'=>$targetParadigm['size']['height']));

            if ($targetParadigm['size']['base64']) {
                // dprintln($targetParadigm['size'], 2, TERM_YELLOW);
                $base64image = base64_encode_data_from_file($illu_file_uri);
                if (strlen($base64image) > (8 * 1024)) {
                    $base64image = '/'.$curimguri;
                }
                // $thumbnails[$sizea['hash']] = array('w'=>$realsize['w'],'h'=>$realsize['w'],'data'=>$base64image);
            }
        }

        if (ANALIZEIMAGE === true) {
            $image = new ImageAnalize($original);
            $image->areaDistribution();
            $mainColors = $image->mainColor();
            $hexcolor1 = ImageAnalize::hsv2rgbhex($mainColors[0]);
            $hexcolor2 = ImageAnalize::hsv2rgbhex($mainColors[1]);

            $m->histogram64 = $image->hist3x64;
            $m->binaryimage = $image->bits;
            $m->freeareas = $image->freeareas;
            $m->color = $hexcolor1;
            $m->color2 = $hexcolor2;
        }

        $m->thumbnail = $base64image;
        $m->facecount = $message->facecount;
        $m->facelist = $message->facelist;
        $m->width = $save_size_width;
        $m->height = $save_size_height;
        $illu = Entity::store($m);
        Log::debug($illu, 'image');
//		 dprintln($illu,1,TERM_GREEN);

        // RETURN
        // when images are ready
        Broker::instance()->send($illu, "ENTITY", "after.create.{$E->name}");

        $r = $illu;
        $r->urn = $illu->urn;
        $r->target = $message->target; // !!! needed in JS - 'hasmanyphotos' etc
        $r->facecount = $message->facecount;
        $r->facelist = $message->facelist;
        $r->image = array('paradigm' => $imageSizeParadigm,'uri' => '/'.$imageSizeUri );
        $r->thumbnail = array('paradigm' => $everyPreviewSizeParadigm, 'uri' => $base64image ? $base64image : $curimguri);
        $r->cropped = $odp;
        $r->width = $save_size_width;
        $r->height = $save_size_height;
        if (ANALIZEIMAGE === true) {
            $r->histogram64 = $image->hist3x64;
            $r->binaryimage = $image->bits;
            $r->freeareas = $image->freeareas;
            $r->color = $hexcolor1;
            $r->color2 = $hexcolor2;
        }
        if ($minsideSource < $minsideTarget) {
            $r->warning = 'Too small image';
        }
        Log::info($r, 'image');
        Log::debug($r->image, 'image');
        Log::debug($r->thumbnail, 'image');
        return $r;
    }


    public function rebuildthumbnails($m)
    {
        $o = $m->urn->resolve();
        $uuid = $o->id;
        $E = $m->urn->entity;

        $uri = '/original/' . $uuid . "." . $o->ext;
        $original =  BASE_DIR . $uri;
        if (!file_exists($original)) {
            $original = BASE_DIR.$o->image->uri;
        }
        if (!file_exists($original)) {
            throw new Exception("No original or image to use {$m->urn}");
        }

        // prefix folder
        $prefixFolderPreview = $prefixFolder = $o->folder;

        //println($original);

        $originalParadigm = Image::analizeImageParadigm($original);
        //dprintln($originalParadigm,1,TERM_GRAY);

        $targetParadigm = $E->imagesettings['mainimage'];
        //dprintln($targetParadigm,1,TERM_RED);

        Image::calcParadigmParameters($targetParadigm, $originalParadigm);

        $parentParadigm = $targetParadigm;
        //$save_size_width = $targetParadigm['size']['width'];
        //$save_size_height = $targetParadigm['size']['height'];

        //$odp = $cropPossibility = Image::analizeCropPossibilitiesBetweenImageParadigms($originalParadigm, $targetParadigm);
        //dprintln($cropPossibility,1,TERM_GREEN);
        // cropPossibility get max
        //$paramsCropResizeConsoleOptions = ImageExternalApp::paradigmDiffAndTargetToCropResizeCommand($cropPossibility, $targetParadigm);
        //dprintln($paramsCropResizeConsoleOptions,1,TERM_GRAY);


        // dprintln($E->imagesettings['previews'],1,TERM_RED); // every preview image sizes paradigm
        $everyPreviewSizeParadigm = $E->imagesettings['previews']['paradigm'];
        $everyPreviewSizeHD = $E->imagesettings['previews']['hd'];

        foreach ($E->imagesettings['previews']['sizes'] as $targetParadigmName => $targetParadigmSizeParams) {
            $targetParadigm = array();
            // get value from all previews paradigm
            $targetParadigm['size'] = $targetParadigmSizeParams;
            $targetParadigm['paradigm'] = $everyPreviewSizeParadigm;
            $targetParadigm['hd'] = $everyPreviewSizeHD;

            // Image::calcParadigmParameters($targetParadigm, $originalParadigm);
            Image::calcParadigmParameters($targetParadigm, $parentParadigm);
            //dprintln($originalParadigm,2,TERM_GRAY);
            //dprintln($targetParadigm,2,TERM_GREEN);
            $minsideSource = min($originalParadigm['ow'], $originalParadigm['oh']);
            $minsideTarget = min($targetParadigm['size']['width'], $targetParadigm['size']['height']);

            $cropPossibility = Image::analizeCropPossibilitiesBetweenImageParadigms($originalParadigm, $targetParadigm);
            //dprintln($cropPossibility,2,TERM_GREEN);
            // cropPossibility get max
            $paramsCropResizeConsoleOptions = ImageExternalApp::paradigmDiffAndTargetToCropResizeCommand($cropPossibility, $targetParadigm);
            //dprintln($paramsCropResizeConsoleOptions,2,TERM_GRAY);

            // RESIZE IMAGE
            $store = 'thumb';
            // $filename = $uuid;
            $curimgPath = join('/', array( $store, $E->getFolderName(), $targetParadigmName, $prefixFolderPreview ));
            $curimgFile = $o->uri.'.'.$o->ext;
            $curimguri = $curimgPath . $curimgFile;
            $illu_file_uri = BASE_DIR.'/'.$curimguri;
            $IMGURI[] = $curimguri;
            mkdir(BASE_DIR.'/'.$curimgPath, octdec(FS_MODE_DIR), true);
            //dprintln($illu_file_uri,2,TERM_VIOLET);
            ImageExternalApp::processImage($original, $illu_file_uri, $paramsCropResizeConsoleOptions);
        }
        $r = new Message();
        if ($minsideSource < $minsideTarget) {
            $r->warning = 'Too small image';
        }
        return $r;
    }


    public function fromXMLextractor(&$dom)
    {
        $domx = new DOMXPath($dom);
        $entries = $domx->evaluate("//media/image");
        foreach ($entries as $n) {
            $f = $n->getAttribute('name');
            $uri = $n->getAttribute('uri');
            dprintln("$f $uri", 2, TERM_VIOLET);
        }
    }

    public function toXMLdecorator(&$dom, &$datarow)
    {
        // $datarow->original

        //$imagepath = $datarow->image->uri;
        //if (!$datarow->image->uri && $datarow->thumbnail) $imagepath = $datarow->thumbnail->uri;

        $domx = new DOMXPath($dom);
        $entries = $domx->evaluate("//entity");
        foreach ($entries as $n) {
            $domentity = $n;
        }
        $xmedia = $dom->createElement("media");
        $domentity->appendChild($xmedia);

        foreach ($datarow->entitymeta->mediaoptions as $hash => $size) {
            if (!$datarow->$hash) {
                continue;
            }
            $imagepath = $datarow->$hash->uri;
            $file = realpath(BASE_DIR.$imagepath);
            if (!$file) {
                return;
            } // skip if file not exists
            if ($file) {
                // file exists

                $xfield = $dom->createElement("image");
                $xfield->setAttribute('name', $hash);
                $xfield->setAttribute('uri', $imagepath);
                $base64image = base64_encode_data_from_file($file);
                $text = $dom->createTextNode($base64image);
                $xfield->appendChild($text);
                $xmedia->appendChild($xfield);
            }
        }
    }

    /**
    dont allow any ops on 16Mp+ files.
    image security - max size ratio, max width or height. check content type
    */
    public function precheck($m)
    {
        /**
        dprintln("PRE CHECK",1, TERM_VIOLET);
        $originalsize = Image::size($m->origin, true);
        if ($m->destination && $m->destination->entity->options['maxmp'] && $originalsize['width'] * $originalsize['height'] > $m->destination->entity->options['maxmp']) return new Message('{"error":1}');
        if ($m->destination && $m->destination->entity->options['maxfilesize'] && $originalsize['filesize'] > $m->destination->entity->options['maxfilesize']) return new Message('{"error":2}');
        if ($m->destination)
        {
            if ($m->destination->entity->options['trim'])
            {
                dprintln('TRIM',1,TERM_GREEN);
                ImageFX::trim($m->origin);
            }
        }
        $m = new Message();
        $m->store = true;
        return $m;
        */
    }

    // NAMING RULES
    private function optimalName($originalName, $E, $fallbackName)
    {
        // dprintln($originalName, 1, TERM_GRAY);
        if ($E->option('filenaming') == 'auto' || $E->option('filenaming') == 'originals') {
            $modeNamingOriginal = true;
            if ($E->option('filenaming') == 'auto') {
                // NAME FROM XMP/IPTC IF EXISTS CHECK IF NOT IS NOT IMG_0001 SOMETHING OR FALLBACK TO UUID
            }
            $TARGET_FILENAME = $originalName;
            // NORMALIZE FILENAME
            $decoded = htmlspecialchars_decode($TARGET_FILENAME);
            $decoded = str_replace('&apos;', '', $TARGET_FILENAME);
            $TARGET_FILENAME = translit($decoded, SystemLocale::$DEFAULT_LANG);
        } else {
            // $E->option('filenaming') == 'uuid'

            $TARGET_FILENAME = $fallbackName;
        }
        return $TARGET_FILENAME;
    }

    // TITLING RULES. metadata or uri2title or blank. metadata or blank. // title is blank by default
    private function optimalTitle($original, $E)
    {
        $namingDone = false;
        // redefine title with xmp/iptc metadata
        if ($E->option('metadata')) {
            // true or array(used fields from metadata)

            $meta = ImageMeta::getExif($original);
            if (count($meta)) {
                //dprintln($meta,1,TERM_GRAY);
                $metadataFound = true;
            } else {
                $metadataFound = false;
            }
            if ($meta['title']) {
                $opttitle = $meta['title'];
                $namingDone = true;
            }
            if ($meta['description']) {
                $optanons = $meta['description'];
            }
        }
        if (!$namingDone) {
            if ($E->option('titling') == 'parent') {
                throw Exception("Legacy option titling = parent");
            } // $opttitle = $parentTitle;
            elseif ($E->option('titling') == 'filename') {
                throw Exception("Legacy option titling = filename");
            } // $opttitle = $parentUri;
            elseif ($E->option('titling') == 'uri2title') {
                // TODO from $parentUri get file uri
                throw Exception("Legacy option titling = uri2title");
                // if (Utils::textHasSense($parentUri)) $opttitle = Utils::uri2title($parentUri);
            }
        }
        // titleling = auto
        // TODO title = ? to left title null
        return array('title'=>$opttitle,'description'=>$optanons,'taken'=>$meta['taken']);
    }

    private function rebuild($message)
    {
        /**
        load by urn
        get parent photo original
        process sizes (uri is in db)
        TODO! use uri from DB (if file was renamed)
        */
        throw new Exception("REBUILD UNIMPLEMENTED");
        $m = new Message();
        $m->action = 'extend';
        $m->rebuild = true;
        $m->urn = $message->urn;
        $m->from = $message->urn->resolve()->photo->urn;
        //dprintln($m,1,TERM_VIOLET);
        return $m->deliver();
        //$m->source = $message;
    }

    public static function extendFields(&$datarow, $E)
    {
        // media servers - host, path variative
        // naming - old need conversion - media/E/id_xsize to thumb/E/xsizename/id
        // legacy point - main image - with /media/name_0 when no f:'folder' used
        // thumb in db as base64
        if (!$datarow['id']) {
            return;
        } else {
            $fext = $datarow['ext'];
            if (strlen($datarow['folder'])) {
                $folder = $datarow['folder'];
            }
            $media_server = (int) $datarow['mediaserver'];
            // Original
            $datarow['original'] = realpath(BASE_DIR."/original/{$datarow['id']}.".$datarow['ext']) ? 'yes' : 'no';
            $host = ''; // mediaservers
            $path = '/media';
            $imguri = "{$host}{$path}/{$E->getFolderName()}/{$folder}{$datarow['uri']}.{$fext}";
            $img = array('uri' => $imguri);
            $datarow['image'] = $img;

            foreach ($E->imagesettings['previews']['sizes'] as $targetParadigmName => $targetParadigmSizeParams) {
                $path = '/thumb';
                $imguri = "{$host}{$path}/{$E->getFolderName()}/$targetParadigmName/{$folder}{$datarow['uri']}.{$fext}";
                $img = array('uri' => $imguri);
                $datarow[$targetParadigmName] = $img;
            }
                        // base 64 data
            $b64 = $datarow['thumbnail'];
            if ($b64) {
                $datarow['thumbnail'] = array('base64data'=>$b64);
            }

            /**
            foreach ($E->mediaoptions as $hash => $size)
            {
                if ($hash == 'image')
                {
                    if (!$folder) $suffix = '_0'; // legacy
                    if ($media_server && ENABLE_MEDIASERVERS_ON_RENDER_PAGE === true)
                    {
                        $host = "http://i{$media_server}.".HOST;
                        $path = '';
                    }
                    else
                    {
                        $path = '/media';
                    }
                    $imguri = "{$host}{$path}/{$E->name}/{$folder}{$datarow['uri']}{$suffix}.{$fext}";
                    $imgpath = "/media/{$E->name}/{$folder}{$datarow['uri']}{$suffix}.{$fext}";
                    $imgsize = getimagesize(BASE_DIR.$imgpath);
                    if (!$imgsize)
                    {
                        Log::info("No main image {$imgpath}. Lazy image disabled",'lostimg');
                    }
                    $img = array('uri' => $imguri, 'width'=> $imgsize[0], 'height' => $imgsize[1]);
                    if (strpos($size,'hd')) $img['hd'] = "{$path}/{$E->name}/{$folder}hd/{$datarow['uri']}{$suffix}.{$fext}";
                    $datarow[$hash] = new Message($img);
                }
                else // non pri image size - thumbs
                {
                    if ($media_server && ENABLE_MEDIASERVERS_ON_RENDER_PAGE === true)
                    {
                        if (USE_MEDIASERVERS_FOR_THUMBS === true) $thumbMediaServer = $media_server;
                        $host = "http://s{$thumbMediaServer}.".HOST;
                        $path = '';
                    }
                    else
                    {
                        $path = '/thumb';
                    }
                    $imguri = "{$host}{$path}/{$E->name}/$hash/{$datarow['id']}.{$fext}";
                    $imgpath = "/thumb/{$E->name}/$hash/{$datarow['id']}.{$fext}";
                    $imgsize = getimagesize(BASE_DIR.$imgpath);
                    //Log::info($imgpath.' : '.$hash.'*'.$size,'xa');
                    //Log::info(json_encode($imgsize),'xa');
                    if (!$imgsize)
                    {
                        Log::info("No thumb image {$imgpath}. Lazy image disabled",'lostimg');
                    }
                    $img = array('uri' => $imguri, 'width'=> $imgsize[0], 'height' => $imgsize[1]);
                    if (strpos($size,'hd')) $img['hd'] = "{$path}/{$E->name}/{$folder}hd/{$datarow['uri']}{$suffix}.{$fext}";
                    $datarow[$hash] = new Message($img);
                }
            }
            */
        }
    }

    // USED ONLY IN OLD BEFORE DELETE IMG
    public static function filePathes($d)
    {
        $E = $d->urn->entity;

        $from = $d->photo;
        if (!count($from)) {
            return array();
        }
        $originalFile = $from->file;
        if (!count($originalFile)) {
            $oext = ($from->ext) ? $from->ext : 'jpg';
            $original = realpath(BASE_DIR.'/original/'.$from->id.'.'. $oext);
        } else {
            $oext = $originalFile->mediatype ? $originalFile->mediatype : 'jpg';
            $original = realpath(BASE_DIR.'/original/'.$originalFile->id.'.'.$originalFile->mediatype);
        }
        $f[] = realpath($original);
        /**
        $sizesCount = count(explode(' ', $E->mediaoptions));
        if (MEDIASERVERS > 1)
            $TARGET_FILENAME = "{$d->uri}";
        else
            $TARGET_FILENAME = "{$d->urn->uuid}";
        $f[]= realpath(BASE_DIR."/original/{$d->urn->uuid}.{$d->ext}");
        for ($i=0;$i<$sizesCount;$i++)
            $f[]= realpath(BASE_DIR."/media/{$E->name}/{$TARGET_FILENAME}_{$i}.{$d->ext}");
        */
        return $f;
    }
}
