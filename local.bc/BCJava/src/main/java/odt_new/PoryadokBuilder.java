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
public class PoryadokBuilder {
    public static void main(String[] args) {
        try {
            TextDocument document =TextDocument.newTextDocument();

            Header docHeader = document.getHeader();
            Table table = docHeader.addTable(4, 4);

            Font Fontfooter = new Font("Arial", StyleTypeDefinitions.FontStyle.REGULAR, 10, Color.GRAY);

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
            discuss.add("Лавров");
            discuss.add("Ларцев");
            String myString=discuss.toString();

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

            PoryadokInfo info =new PoryadokInfo();
            String name=info.name;
            String goals=info.goals;
            String realmuse=info.realmuse;
            String reaponsib=info.responsib;
            String resource=info.resourse;
            String procedure=info.procedure;
            String reports=info.reports;
            String docforlink=info.docforlink;

            PoryadokInfo.StructPoryadokParagraph block1 = new PoryadokInfo.StructPoryadokParagraph();
            block1.sectiontitle= "Дополнительный раздел 1";
            block1.sectiontext="Текст к дополнительному разделу 1";
            info.paragraphs.add(block1);

            PoryadokInfo.StructPoryadokParagraph block2 = new PoryadokInfo.StructPoryadokParagraph();
            block2.sectiontitle= "Дополнительный раздел 2";
            block2.sectiontext="Текст к дополнительному разделу 2";
            info.paragraphs.add(block2);



            Paragraph paragraph1 = document.addParagraph(name);
            paragraph1.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Font myFont = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 14, Color.BLACK);
            paragraph1.setFont(myFont);
            Paragraph paragraph11 = document.addParagraph("");

            Paragraph paragraph2 = document.addParagraph("Цель");
            document.addParagraph("");
            paragraph2.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Font myFont2 = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 12, Color.BLACK);
            paragraph2.setFont(myFont2);
            Paragraph paragraph21 = document.addParagraph(goals);
            Paragraph paragraph22 = document.addParagraph("");

            Paragraph paragraph3 = document.addParagraph("Область применения");
            document.addParagraph("");
            paragraph3.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph3.setFont(myFont2);
            Paragraph paragraph31 = document.addParagraph(realmuse);
            Paragraph paragraph32 = document.addParagraph("");

            Paragraph paragraph4 = document.addParagraph("Ответственность и полномочия");
            document.addParagraph("");
            paragraph4.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph4.setFont(myFont2);
            Paragraph paragraph41 = document.addParagraph(reaponsib);
            Paragraph paragraph42 = document.addParagraph("");

            Paragraph paragraph5 = document.addParagraph("Материалы и оборудование(ресурсы)");
            document.addParagraph("");
            paragraph5.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph5.setFont(myFont2);
            Paragraph paragraph51 = document.addParagraph(resource);
            Paragraph paragraph52 = document.addParagraph("");

            Paragraph paragraph6 = document.addParagraph("Процедура");
            document.addParagraph("");
            paragraph6.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph6.setFont(myFont2);
            Paragraph paragraph61 = document.addParagraph(procedure);
            Paragraph paragraph62 = document.addParagraph("");

            for(int i=0; i<info.paragraphs.size(); i++) {
                Paragraph paragraph7= document.addParagraph(info.paragraphs.get(i).sectiontitle);
                paragraph7.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
                paragraph7.setFont(myFont2);
                document.addParagraph("");
                Paragraph paragraph8=document.addParagraph(info.paragraphs.get(i).sectiontext);
                document.addParagraph("");
            }

            Paragraph paragraph8 = document.addParagraph("Отчет");
            document.addParagraph("");
            paragraph8.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph8.setFont(myFont2);
            Paragraph paragraph81 = document.addParagraph(reports);
            Paragraph paragraph82 = document.addParagraph("");

            Paragraph paragraph9 = document.addParagraph("Документы ");
            document.addParagraph("");
            paragraph9.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph9.setFont(myFont2);
            Paragraph paragraph91 = document.addParagraph(docforlink);
            Paragraph paragraph92 = document.addParagraph("");



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

            document.save("/home/anton/printForms/poryadok.odt");

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
