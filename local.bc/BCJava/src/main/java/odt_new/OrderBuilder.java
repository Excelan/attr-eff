package odt_new;


import org.odftoolkit.odfdom.type.Color;
import org.odftoolkit.simple.TextDocument;
import org.odftoolkit.simple.style.Font;
import org.odftoolkit.simple.style.StyleTypeDefinitions;
import org.odftoolkit.simple.table.Cell;
import org.odftoolkit.simple.table.Table;
import org.odftoolkit.simple.text.Footer;
import org.odftoolkit.simple.text.Header;
import org.odftoolkit.simple.text.Paragraph;

import java.util.LinkedList;
import java.util.List;

/**
 * Created by Iryna on 04.01.2016.
 */
public class OrderBuilder {
    public static void main(String[] args) {
        try {
            TextDocument document =TextDocument.newTextDocument();

            //landscape orientation of one page
            /*MasterPage master1=MasterPage.getOrCreateMasterPage(document, "Landscape");
            *master1.setPrintOrientation(PrintOrientation.enumValueOf(PrintOrientation.LANDSCAPE));
            */
            HeaderFooterInfo header= new HeaderFooterInfo();
            String url=header.imgurl;
            String docclass=header.documentclass;
            String doctype=header.documenttype;
            String id=header.iddoc;
            String docversion=header.docversion;
            String docname=header.docname;
            String numbofpage=header.numbofpage;

            String createdoc=header.createdoc;
            String approvedoc=header.approvedoc;
            List<String> discuss=header.discuss;
            discuss=new LinkedList<String>();
            discuss.add("Lavrov");
            discuss.add("Lartsev");
            String myString=discuss.toString();


            OrderInfo info=new OrderInfo();
            String number=info.number;
            String realdate=info.realdate;
            String ordername=info.title;
            String text=info.preamble;
         //   String moretext=info.item;
            String director=info.director;
            LinkedList<String> moretext = new LinkedList<String>();
            moretext.add("Первый параграф");
            moretext.add("Второй параграф");

            Font myFont = new Font("Arial", StyleTypeDefinitions.FontStyle.REGULAR, 10, org.odftoolkit.odfdom.type.Color.GRAY);

            Header docHeader = document.getHeader();
            Table table = docHeader.addTable(4, 4);

           // Cell cellimage = table.getCellByPosition(0, 0);
           // cellimage.setImage(java.net.URI.create("file:////home/anton/1.png"));

           // table.getCellByPosition(0, 0).setImage(java.net.URI.create(url));
            table.getCellByPosition(1, 0).setStringValue("Класс документа");
            table.getCellByPosition(1, 2).setStringValue("Тип документа");
            table.getCellByPosition(2, 0).setStringValue("Id");
            table.getCellByPosition(2, 2).setStringValue("Версия документа");
            table.getCellByPosition(3, 0).setStringValue("Название документа");
            table.getCellByPosition(3, 2).setStringValue("Страница документа");
           // table.getCellByPosition(0, 4).setStringValue("");

            table.getCellByPosition(0, 0).setFont(myFont);
            table.getCellByPosition(0, 0).setFont(myFont);
            table.getCellByPosition(1, 0).setFont(myFont);
            table.getCellByPosition(1, 2).setFont(myFont);
            table.getCellByPosition(2, 0).setFont(myFont);
            table.getCellByPosition(2, 2).setFont(myFont);
            table.getCellByPosition(3, 0).setFont(myFont);
            table.getCellByPosition(3, 2).setFont(myFont);

            table.getCellByPosition(1, 1).setStringValue(docclass);
            table.getCellByPosition(1, 3).setStringValue(doctype);
            table.getCellByPosition(2, 1).setStringValue(id);
            table.getCellByPosition(2, 3).setStringValue(docversion);
            table.getCellByPosition(3, 1).setStringValue(docname);
            table.getCellByPosition(3, 3).setStringValue(String.valueOf(numbofpage));

            //<tt> PageNumberField </tt>
           // OdfStylePageLayout
            Paragraph date = document.addParagraph(realdate);
            Paragraph numberoforder = document.addParagraph("Приказ №"+number);
            numberoforder.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Font myFont2 = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 14, Color.BLACK);
            numberoforder.setFont(myFont2);

            Paragraph title=document.addParagraph(ordername);
            title.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Font myFont3 = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 12, Color.BLACK);
            title.setFont(myFont3);
            Paragraph space1=document.addParagraph("");

            Paragraph maintext=document.addParagraph(text);
            Paragraph spaceextra=document.addParagraph("");
            Paragraph ordering=document.addParagraph("Приказываю:");
            Paragraph space2=document.addParagraph("");
            ordering.setFont(myFont3);

            for(int i=0;i<moretext.size();i++) {
                Paragraph bodytext = document.addParagraph(i+1+"."+moretext.get(i));
                Paragraph space3 = document.addParagraph("");
            }

            for(int i=0;i<25;i++) {
            document.addParagraph("");
            }

            Paragraph directore=document.addParagraph("Директор ООО \"ХФК Биокон\"");
            Paragraph directorname=document.addParagraph(director);

            /*
            TextDocument target=TextDocument.loadDocument("D:/Quick_odt/quick_odt/order4.odt");
            Paragraph p1=target.getParagraphByIndex(3, true);
            target.insertContentFromDocumentAfter(document, p1, true);
            target.insertContentFromDocumentBefore(document, p1,false);
            */

            Footer footer = document.getFooter();
            table = footer.addTable(4, 2);

            Cell cellByPosition1 = table.getCellByPosition(0, 0);
            cellByPosition1.setStringValue("Разработал:");
            Cell cellByPosition2 = table.getCellByPosition(1, 0);
            cellByPosition2.setStringValue("Согласовал(и):");
            Cell cellByPosition3 = table.getCellByPosition(0, 2);
            cellByPosition3.setStringValue("Утвердил:");
            Cell cellByPosition4 = table.getCellByPosition(1, 2);
            cellByPosition4.setStringValue("");

            cellByPosition1.setFont(myFont);
            cellByPosition2.setFont(myFont);
            cellByPosition3.setFont(myFont);
            cellByPosition4.setFont(myFont);

            Cell cellByPosition5 = table.getCellByPosition(0, 1);
            cellByPosition5.setStringValue(createdoc);
            Cell cellByPosition6 = table.getCellByPosition(1,1);
            cellByPosition6.setStringValue(myString);
            Cell cellByPosition7 = table.getCellByPosition(0, 3);
            cellByPosition7.setStringValue(approvedoc);
            Cell cellByPosition8 = table.getCellByPosition(1, 3);
            cellByPosition8.setStringValue("");

            document.save("/home/anton/printForms/OrderBuilder.odt");

    } catch (Exception e) {
        e.printStackTrace();
    }
}
}


