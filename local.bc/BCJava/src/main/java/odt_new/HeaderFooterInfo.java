package odt_new;

import java.util.List;

/**
 * Created by Iryna on 06.01.2016.
 */
public class HeaderFooterInfo {

    public String imgurl;
    public String documentclass="Должностная инструкция";
    public String documenttype="по жалобе";
    public String iddoc="1";
    public String docversion="13";
    public String docname="Doljn-13-45";
  public String numbofpage="1 из 2";

    public  String createdoc="Иванов";
    public  String approvedoc="Петров";
    public List <String> discuss;

    public HeaderFooterInfo() {
        this.imgurl = imgurl;
        this.documentclass = documentclass;
        this.documenttype = documenttype;
        this.iddoc = iddoc;
        this.docversion = docversion;
        this.docname = docname;
        this.numbofpage = numbofpage;
        this.createdoc = createdoc;
        this.approvedoc = approvedoc;
        this.discuss=discuss;
    }
}

