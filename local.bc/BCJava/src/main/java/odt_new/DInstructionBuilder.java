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
public class DInstructionBuilder {
    public static void main(String[] args) {
        try {
            TextDocument document =TextDocument.newTextDocument();

            Header docHeader = document.getHeader();
            Table table = docHeader.addTable(4, 4);
            //Cell cell = table.getCellByPosition(1, 0);

            Font Fontfooter = new Font("Arial", StyleTypeDefinitions.FontStyle.REGULAR, 10, org.odftoolkit.odfdom.type.Color.GRAY);

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

           // PageNumberField numberField= Fields.createCurrentPageNumberField(document.newParagraph(""));

           // Cell cellimage = table.getCellByPosition(0, 0);
           // cellimage.setImage(java.net.URI.create(url));
            // table.getCellByPosition(0, 0).setImage(java.net.URI.create(url));
            table.getCellByPosition(1, 0).setStringValue("Класс документа");
            table.getCellByPosition(1, 2).setStringValue("Тип документа");
            table.getCellByPosition(2, 0).setStringValue("Id");
            table.getCellByPosition(2, 2).setStringValue("Версия документа");
            table.getCellByPosition(3, 0).setStringValue("Название документа");
            table.getCellByPosition(3, 2).setStringValue("Страница документа");

            // table.getCellByPosition(0, 4).setStringValue("");

            table.getCellByPosition(0, 0).setFont(Fontfooter);
            table.getCellByPosition(0, 0).setFont(Fontfooter);
            table.getCellByPosition(1, 0).setFont(Fontfooter);
            table.getCellByPosition(1, 2).setFont(Fontfooter);
            table.getCellByPosition(2, 0).setFont(Fontfooter);
            table.getCellByPosition(2, 2).setFont(Fontfooter);
            table.getCellByPosition(3, 0).setFont(Fontfooter);
            table.getCellByPosition(3, 2).setFont(Fontfooter);

            table.getCellByPosition(1, 1).setStringValue(docclass);
            table.getCellByPosition(1, 3).setStringValue(doctype);
            table.getCellByPosition(2, 1).setStringValue(id);
            table.getCellByPosition(2, 3).setStringValue(docversion);
            table.getCellByPosition(3, 1).setStringValue(docname);
            table.getCellByPosition(3, 3).setStringValue(numbofpage);

            DInstructionInfo lala =new DInstructionInfo();
            String name=lala.instructionname;
            String par1=lala.position;
            String par2=lala.duty;
            String par3=lala.authority;
            String par4 = lala.responsibility;

            Paragraph paragraph1 = document.addParagraph("Должностная инструкция");
            paragraph1.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Font myFont = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 14, Color.BLACK);
            paragraph1.setFont(myFont);
            Paragraph paragraph11 = document.addParagraph(name);
            Font myFont3=new Font ("Arial", StyleTypeDefinitions.FontStyle.BOLD, 12, Color.BLACK);
            paragraph11.setFont(myFont3);
            paragraph11.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Paragraph paragraph12 = document.addParagraph("");

            Paragraph paragraph2 = document.addParagraph("1.Общие положения");
            paragraph2.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Paragraph paragraph21 = document.addParagraph(par1);
            Font myFont2 = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 12, Color.BLACK);
            paragraph2.setFont(myFont2);

            Paragraph paragraph3 = document.addParagraph("2.Функциональные обязанности");
            paragraph3.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Paragraph paragraph31 = document.addParagraph(par2);
            paragraph3.setFont(myFont2);

            Paragraph paragraph4 = document.addParagraph("3.Права");
            paragraph4.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Paragraph paragraph41 = document.addParagraph(par3);
            paragraph4.setFont(myFont2);


            Paragraph paragraph5 = document.addParagraph("4.Ответственность");
            paragraph5.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Paragraph paragraph51 = document.addParagraph(par4);
            paragraph5.setFont(myFont2);


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


            cellByPosition1.setFont(Fontfooter);
            cellByPosition2.setFont(Fontfooter);
            cellByPosition3.setFont(Fontfooter);
            cellByPosition4.setFont(Fontfooter);


            Cell cellByPosition5 = table.getCellByPosition(0, 1);
            cellByPosition5.setStringValue(createdoc);
            Cell cellByPosition6 = table.getCellByPosition(1,1);
            cellByPosition6.setStringValue(myString);
            Cell cellByPosition7 = table.getCellByPosition(0, 3);
            cellByPosition7.setStringValue(approvedoc);
            Cell cellByPosition8 = table.getCellByPosition(1, 3);
            cellByPosition8.setStringValue("");

            document.save("/home/anton/printForms/dinstruction.odt");

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
