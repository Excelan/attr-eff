package odt_new;


import org.odftoolkit.simple.TextDocument;
import org.odftoolkit.simple.style.Border;
import org.odftoolkit.simple.style.Font;
import org.odftoolkit.simple.style.MasterPage;
import org.odftoolkit.simple.style.StyleTypeDefinitions;
import org.odftoolkit.simple.table.Cell;
import org.odftoolkit.simple.table.Table;
import org.odftoolkit.simple.text.Footer;
import org.odftoolkit.simple.text.Header;
import org.odftoolkit.simple.text.Paragraph;

import java.util.LinkedList;
import java.util.List;

import static org.odftoolkit.simple.style.StyleTypeDefinitions.PrintOrientation.LANDSCAPE;

/**
 * Created by Iryna on 04.01.2016.
 */
public class CapaBuillder {
    public static void main(String[] args) {
        try {
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
            discuss.add("Лавров");
            discuss.add("Ларцев");
            String myString=discuss.toString();

            CapaInfo cp = new CapaInfo();

            String descriptiondeviation =cp.decriptiondeviation;
            List <String> risks;
            risks=new LinkedList<String>();
            risks.add("Риск 1");
            risks.add("Риск 2");
            String allRisk=risks.toString();

            CapaInfo.TableCapaRow citc = new CapaInfo.TableCapaRow();
            citc.descriptioncorrection= "Нанести красную линию";
            citc.eventplace="Склад 1";
            citc.controlresponsible="Иванов";
            citc.realizationdate="31/10/2016";
            citc.factdate="31/12/2016";
            citc.capastatus="не просмотрено";
            cp.tablerows.add(citc);

            CapaInfo.TableCapaRow citc2 = new CapaInfo.TableCapaRow();
            citc2.descriptioncorrection= "Голубая линия ";
            citc2.eventplace="Вторая строчка";
            citc2.controlresponsible="Петров";
            citc2.realizationdate="31/07/2016";
            citc2.factdate="31/08/2016";
            citc2.capastatus="сделано";
            cp.tablerows.add(citc2);

            CapaInfo.TableCapaRow citc3= new CapaInfo.TableCapaRow();
            citc3.descriptioncorrection= "Зеленая линия ";
            citc3.eventplace="Третья строчка";
            citc3.controlresponsible="Кузьма";
            citc3.realizationdate="31/07/2016";
            citc3.factdate="31/08/2016";
            citc3.capastatus="Скоро будет сделано";
            cp.tablerows.add(citc3);

            //найти элементы в каждой строчки
            //System.out.println(cp.eventplace);
            System.out.println(citc.capastatus);
            System.out.println(cp.tablerows.size());
            cp.tablerows.forEach(tr ->
                            System.out.println(tr.descriptioncorrection)
            );
            cp.tablerows.forEach(tr ->
                            System.out.println(tr.eventplace)
            );
            cp.tablerows.forEach(tr ->
                            System.out.println(tr.controlresponsible)
            );
            cp.tablerows.forEach(tr ->
                            System.out.println(tr.realizationdate)
            );
            cp.tablerows.forEach(tr ->
                            System.out.println(tr.factdate)
            );
            cp.tablerows.forEach(tr ->
                            System.out.println(tr.capastatus)
            );

            // найти все строчки в таблице
            for(int i=0; i<cp.tablerows.size(); i++){
                System.out.println(cp.tablerows.get(i).toString());
                //доступ к полю строчки
                switch (cp.tablerows.get(i).capastatus) {
                }
            }

            TextDocument document = TextDocument.newTextDocument();
            //landscape orientation of one page
            MasterPage master1=MasterPage.getOrCreateMasterPage(document, "Landscape");
            master1.setPrintOrientation(LANDSCAPE);


            Font myFont = new Font("Arial", StyleTypeDefinitions.FontStyle.REGULAR, 10, org.odftoolkit.odfdom.type.Color.GRAY);

            Header docHeader = document.getHeader();
            Table table = docHeader.addTable(4, 4);

          //  PageNumberField numberField= Fields.createCurrentPageNumberField(document.newParagraph("Curremt page:"));


          // Cell cellimage = table.getCellByPosition(0, 0);
          // cellimage.setImage(java.net.URI.create("file:///~odf.png"));
            //cellimage.se

          //  table.getCellByPosition(0, 0).setImage(java.net.URI.create(url));
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
            table.getCellByPosition(3, 3).setStringValue(String.valueOf(numbofpage));

            // Document doc=TextDocument.loadDocument("D:/Quick_odt/quick_odt/capastyle2.odt");

            // TableTemplate template= doc.LoadTableTemplateFromForeignTable(new FileInputStream("D:/Quick_odt/quick_odt/capastyle3.odt"),"Table1");

            Table table1 = document.addTable(2, 2);
         //    table1.applyStyle(template);
            Cell cell = table1.getCellByPosition(0, 0);
            cell.setStringValue("Отклонение:");
            Cell cell2 = table1.getCellByPosition(1, 0);
            cell2.setStringValue("Риски:");

            cell.setFont(myFont);
            cell2.setFont(myFont);

            Cell cell11 = table1.getCellByPosition(0, 1);
            cell11.setStringValue(descriptiondeviation);
            Cell cell12 = table1.getCellByPosition(1, 1);
            cell12.setStringValue(allRisk);


            Font myFontbig = new Font("Arial", StyleTypeDefinitions.FontStyle.REGULAR, 14, org.odftoolkit.odfdom.type.Color.BLACK);
            cell11.setFont(myFontbig);
            cell12.setFont(myFontbig);


            Border borderbase=new Border(org.odftoolkit.odfdom.type.Color.WHITE,2, StyleTypeDefinitions.SupportedLinearMeasure.PT);


         //   table1.getCellByPosition(0, 0).setBorders(StyleTypeDefinitions.CellBordersType.ALL_FOUR, borderbase);
         //   table1.getCellByPosition(0, 1).setBorders(StyleTypeDefinitions.CellBordersType.ALL_FOUR, borderbase);
         //   table1.getCellByPosition(1, 3).setBorders(StyleTypeDefinitions.CellBordersType.ALL_FOUR, borderbase);
         //   table1.getCellByPosition(0, 1).setBorders(StyleTypeDefinitions.CellBordersType.ALL_FOUR, borderbase);
         //   table1.getCellByPosition(0, 3).setBorders(StyleTypeDefinitions.CellBordersType.ALL_FOUR, borderbase);
         //   table1.getCellByPosition(1, 0).setBorders(StyleTypeDefinitions.CellBordersType.ALL_FOUR, borderbase);
         //   table1.getCellByPosition(0, 2).setBorders(StyleTypeDefinitions.CellBordersType.ALL_FOUR, borderbase);
         //   table1.getCellByPosition(1, 2).setBorders(StyleTypeDefinitions.CellBordersType.ALL_FOUR, borderbase);


            Paragraph paragraph = document.addParagraph("");
          // Image image=Image.newImage(paragraph, java.net.URI.create("D:/Quick_odt/quick_odt/images/odf.png"));

            int numrows=cp.tablerows.size();

            Table table2 = document.addTable(numrows+1, 6);
            Cell cell5 = table2.getCellByPosition(0, 0);
            cell5.setStringValue("Описание корректирующих мероприятий");
            Cell cell6 = table2.getCellByPosition(1, 0);
            cell6.setStringValue("Место проведения мероприятия");
            Cell cell7 = table2.getCellByPosition(2, 0);
            cell7.setStringValue("Ответственный за проведение корректирующего мероприятия");
            Cell cell8 = table2.getCellByPosition(3, 0);
            cell8.setStringValue("Плановая дата реализации корректирующего мероприятия");
            Cell cell9 = table2.getCellByPosition(4, 0);
            cell9.setStringValue("Фактическая дата реализации корректирующего мероприятия");
            Cell cell10 = table2.getCellByPosition(5, 0);
            cell10.setStringValue("Статус реализации корректирующего мероприятия");

            cell5.setFont(myFont);
            cell6.setFont(myFont);
            cell7.setFont(myFont);
            cell8.setFont(myFont);
            cell9.setFont(myFont);
            cell10.setFont(myFont);

            for(int i=0; i<cp.tablerows.size(); i++) {
                table2.getCellByPosition(0, i+1).setStringValue(i+1 +" "+cp.tablerows.get(i).descriptioncorrection);
                table2.getCellByPosition(1, i+1).setStringValue(cp.tablerows.get(i).eventplace);
                table2.getCellByPosition(2, i+1).setStringValue(cp.tablerows.get(i).controlresponsible);
                table2.getCellByPosition(3, i+1).setStringValue(cp.tablerows.get(i).realizationdate);
                table2.getCellByPosition(4, i+1).setStringValue(cp.tablerows.get(i).factdate);
                table2.getCellByPosition(5, i+1).setStringValue(cp.tablerows.get(i).capastatus);
            }

            Paragraph paragraph2=document.addParagraph("");

            Footer footer = document.getFooter();
            table = footer.addTable(4, 2);
            Cell cellByPosition1 = table.getCellByPosition(0,0);
            cellByPosition1.setStringValue("Разработал:");
            Cell cellByPosition2 = table.getCellByPosition(1,0);
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

            document.save("/home/anton/printForms/CapaBuillder.odt");

    } catch (Exception e) {
        e.printStackTrace();
    }
}
}


