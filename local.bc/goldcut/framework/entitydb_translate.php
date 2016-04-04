<?php 
/**
EntityDB_translate
*/

class EntityDB_translate extends EntityDB
{
	/**
	TRANSLATE
	 */
	public function provide_translate($id, $lang, $data)
	{
		$q = "UPDATE ".SQLQT."".$this->E->name."".SQLQT." SET translated_{$lang} = 1, ";
		foreach ($this->E->lang_fields() as $F)
		{
			$fname = $F->name;
			if ( strlen($data->$fname) > 0 )
			{
				// PREPROCESS URI TRANSLIT
				if (is_array($this->E->translit))
				{
					foreach ($this->E->translit as $tf => $tt)
					{
						$data->$tt = translit($data->$tf, $data->lang);
					}
				}

				$secured = EntityStore::fieldSecure($F, $data->$fname);
				$safe = Security::mysql_escape($secured);

                // TODO ???
                if (strlen($safe))
					$ft = $safe;
				else
					$ft = str_replace("'", "’", $data->$fname);

				if ($lang != DEFAULT_LANG)
					$qf[] = SQLQT.$fname."_{$lang}".SQLQT." = '" . $ft . "'";
				else
					$qf[] = SQLQT.$fname."".SQLQT." = '" . $ft . "'";
			}
		}
		$q .= join($qf, ", ");
		$q .= " WHERE id = $id";

		$this->dblink->nquery($q);
	}

}

?>