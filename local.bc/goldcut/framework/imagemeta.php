<?php 
/**
ImageMeta
Also, Photoshop now uses XMP for it's primary metadata, meaning IPTC is only read by Photoshop if XMP is not present.
lib gpl http://www.ozhiker.com/electronics/pjmt/index.html
Because latest Adobe Photoshop (CS) does not embed EXIF data to images any more, but embeds EXIF into XMP instead, I wrote this function to read those SMP tags back to EXIF for further use.
Adobe Photoshop, Adobe Lightroom и, конечно, ProStockMaster поддерживают такой режим работы используя стандарты метаданных IPTC и XMP.
Стандарты IPTC и XMP поддерживаются не для всех типов файлов. JPG, TIFF и форматы Adobe (например, PSD) позволяют работу с метаданными, в то время, как другие – нет.
Запись IPTC данных определена в стандарте в кодировке ISO-8859-1 (“latin1″) – русский там не поддерживается, и программы не справятся с чтением и записью русских букв в этой кодировке
фотографы использовали программы которые умеют записывать XMP (ProStockMaster, к примеру, умеет – он пишет и в XMP, и в IPTC одновременно). Показывет программа, впрочем, только из IPTC – поэтому для русского требуется некоторая доработка – дополнительная закладка XMP рядом с IPTC и EXIF в основном окне программы.
File → File Info…, то откроется диалог редактирования XMP-данных. Там много разных полей, нас интересует группа “Description” (она открывается по умолчанию). В ней можно задать название картинки (Document Title), описание (Description), ключевые слова через запятую (Keywords)
Photoshop также позволяет создавать шаблоны метаданных (Metadata Templates), в которых запоминаются введённые параметры. Это упрощает атрибутирование серий изображений, но всё равно для каждой картинки диалог приходится открывать отдельно.
ACDSee начиная с 8-ой версии содержит в панели “Properties” вкладку “IPTC”. В версии “Pro”, выбрав несколько файлов, можно задать описания для всех их сразу (при помощи инструмента Tools → Batch Set Information, Ctrl+M).
XP - http://www.microsoft.com/en-us/download/details.aspx?id=13518#system-requirements
W7 есть сразу по правому клику

Apple's new version of Aperture (released February 9, 2010) would be supporting XMP. I had been noticing the incremental improvements within Apple's Preview program as more and more XMP fields became visible. My understanding is that in order for Apple programs to support XMP, this functionality had to be available at the OS level first
Aperture 3 can read and write metadata stored in the older IPTC's older binary Information Interchange Model (IIM) and the XMP based IPTC Core. XMP Core support requires XMP support because it uses a sub-set of the old IIM's terms in addition to new terms that can only be stored using XMP. 
! Many basic fields such as Captions and Keywords, Creator (Author), and Copyright Notice can exist in both the IPTC-IIM and IPTC Core, while other newer fields such as the Rights Usage Terms only exist in IPTC Core and these values are stored using XMP.

! This violates the second and third principles of the Metadata Manifesto, namely that "Ownership metadata must never be removed." as well as "Metadata must be written in formats that are understood by all."
http://www.stockartistsalliance.org/metadata-manifesto-1
http://metadatadeluxe.pbworks.com/w/page/47662311/Top%2010%20List%20of%20Embedded%20Metadata%20Properties

http://www.photometadata.org/META-Resources-metadata-types-standards-IPTC-Core-and-extensions

http://www.adobe.com/devnet/xmp.html XMPCore C++ lib, java, as3


*/
class ImageMeta
{
	public static function getExif($fileFullPath)
	{
		if (!function_exists('read_exif_data')) return array("error"=>"no exif lib");
		$exif_ifd0 = read_exif_data($fileFullPath,'IFD0',0);
		$exif_exif = read_exif_data($fileFullPath,'EXIF',0);
		$camIso = $exif_exif['ISOSpeedRatings'];
		$camMake = $exif_ifd0['Make'];
		$camModel = $exif_ifd0['Model'];
		if ($exif_ifd0['DateTime']) $meta['taken'] = strtotime($exif_ifd0['DateTime']);
		if ($exif_exif['iso']) $meta['iso'] = $exif_exif['ISOSpeedRatings'];
		if ($exif_ifd0['Make']) $meta['make'] = $exif_ifd0['Make'];
		if ($exif_ifd0['Model']) $meta['model'] = $exif_ifd0['Model'];
		// GPS
		
		$size = getimagesize ($fileFullPath, $info);
		if(is_array($info))
		{
			$iptc = iptcparse($info["APP13"]);
			if ($iptc['2#005']) $meta['title'] = $iptc['2#005'][0];
			if ($iptc['2#120']) $meta['description'] = $iptc['2#120'][0];
			if ($iptc['2#116']) $meta['copyright'] = $iptc['2#116'][0];
			// 2#025 keys[]
		}
		return $meta;
	}
}




?>