<?php

class Attach extends EManager
{
	protected function config()
	{
		$this->behaviors[] = 'general_crud';
	}
	
	public function create($message)
	{
		$origin = $message->file->origin;
		$file_urn = new URN($message->file->urn);
		$filesize = filesize($origin);
		$uri = $message->file->uri;
		$message->clear('file');
		$message->merge( array('file'=>$file_urn, 'uri' => $uri, 'ext'=>$message->ext, 'id'=>$file_urn->uuid()->toInt(), 'filesize'=>$filesize, 'created' => TimeOp::now()) );

		$attach = Entity::store($message);
		$r = new Message();
		$r->urn = $attach->urn;
		$r->uri = $uri;
		$r->ext = $message->ext;
		$r->icon = array("uri"=>"/goldcut/assets/filetype/{$message->ext}.png");
		return $r;
	}

}
?>