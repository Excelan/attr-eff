package odt_new;

import org.odftoolkit.odfdom.type.Color;
import org.odftoolkit.simple.TextDocument;
import org.odftoolkit.simple.style.Border;
import org.odftoolkit.simple.style.Font;
import org.odftoolkit.simple.style.StyleTypeDefinitions;
import org.odftoolkit.simple.table.Cell;
import org.odftoolkit.simple.table.Table;
import org.odftoolkit.simple.text.Paragraph;

/**
 * Created by Iryna on 04.01.2016.
 */
public class ContractBuilderRSS {
    public static void main(String[] args) {
        try {
            TextDocument document = TextDocument.newTextDocument();

            //Contract RSS

            ContractInfoRSS info = new ContractInfoRSS();
            String number = info.number;
            String place = info.place;
            String contdate = info.contdate;
            String introduction = info.introduction;

            String wordsdefinition=info.wordsdefinition;
            String subjectofcontract=info.subjectofcontract;
            String responsibilityofdoer=info.responsibilityofdoer;
            String responsibility=info.responsibility;
            String priceandterm=info.priceandterm;
            String insurance=info.insurance;
            String accounting=info.accounting;
            String trademarks=info.trademarks;
            String confidentiality=info.confidentiality;
            String timeofcontract=info.timeofcontract;
            String forcemajeure=info.forcemajeure;
            String refuce=info.refuce;
            String fullcontract=info.fullcontract;
            String language=info.language;
            String jurisdiction=info.jurisdiction;
            String otherconditions=info.otherconditions;

            String requisites = info.requisites;
            String requisitesco = info.requisitescont;
            String director = info.director;

            ContractInfoRSS.ApplicationRow row1 = new ContractInfoRSS.ApplicationRow();
            row1.text = "В данном приложении прилагаются блок-схемы";

            ContractInfoRSS.ApplicationRow.MediaRow subrow1=new ContractInfoRSS.ApplicationRow.MediaRow();
            subrow1.urii=("file:///~odf.png");
            subrow1.picturename="Graphic1";
            row1.mediarows.add(subrow1);

            ContractInfoRSS.ApplicationRow.MediaRow subrow2=new ContractInfoRSS.ApplicationRow.MediaRow();
            subrow2.urii=("file:///~odf.png");
            subrow2.picturename="Graphic2";
            row1.mediarows.add(subrow2);

            ContractInfoRSS.ApplicationRow.MediaRow subrow3=new ContractInfoRSS.ApplicationRow.MediaRow();
            subrow3.urii=("file:///~odf.png");
            subrow3.picturename="Graphic3";
            row1.mediarows.add(subrow3);

            info.tablerows.add(row1);

            ContractInfoRSS.ApplicationRow row2 = new ContractInfoRSS.ApplicationRow();
            row2.text = "В данном приложении прилагаются важные детали";
            info.tablerows.add(row2);

            Paragraph paragraph1 = document.addParagraph("Договор №" + number);
            paragraph1.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Font myFont = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 14, Color.BLACK);
            paragraph1.setFont(myFont);
            Paragraph paragraph11 = document.addParagraph("на оказание регулярных услуг");
            paragraph11.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Font myFont3 = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 12, Color.BLACK);
            paragraph11.setFont(myFont3);
            Paragraph paragraph12 = document.addParagraph("");

            Paragraph paragraph214 = document.addParagraph(place);
            paragraph214.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.LEFT);

            Paragraph paragraph215 = document.addParagraph(contdate);
            paragraph215.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.RIGHT);

            Paragraph paragraph2 = document.addParagraph("");
            document.addParagraph("");
            paragraph2.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Font myFont2 = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 12, Color.BLACK);
            paragraph2.setFont(myFont2);
            Paragraph paragraph21 = document.addParagraph(introduction);
            Paragraph paragraph22 = document.addParagraph("");

            Paragraph paragraph3 = document.addParagraph("1.Определение терминов и их толкование");
            document.addParagraph("");
            paragraph3.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph3.setFont(myFont2);
            Paragraph paragraph31 = document.addParagraph(wordsdefinition);
            Paragraph paragraph32 = document.addParagraph("");

            Paragraph paragraph4 = document.addParagraph("2.Предмет договора");
            document.addParagraph("");
            paragraph4.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph4.setFont(myFont2);
            Paragraph paragraph41 = document.addParagraph(subjectofcontract);
            Paragraph paragraph42 = document.addParagraph("");

            Paragraph paragraph5 = document.addParagraph("3.Ответственность исполнителя");
            document.addParagraph("");
            paragraph5.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph5.setFont(myFont2);
            Paragraph paragraph51 = document.addParagraph(responsibilityofdoer);
            Paragraph paragraph52 = document.addParagraph("");

            Paragraph paragraph6 = document.addParagraph("4.Ответственность заказчика");
            document.addParagraph("");
            paragraph6.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph6.setFont(myFont2);
            Paragraph paragraph61 = document.addParagraph(responsibility);
            Paragraph paragraph62 = document.addParagraph("");

            Paragraph paragraph7 = document.addParagraph("5.Цена и сроки оплаты");
            document.addParagraph("");
            paragraph7.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph7.setFont(myFont2);
            Paragraph paragraph71 = document.addParagraph(priceandterm);
            Paragraph paragraph72 = document.addParagraph("");

            Paragraph paragraph8 = document.addParagraph("6.Страхование");
            document.addParagraph("");
            paragraph8.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph8.setFont(myFont2);
            Paragraph paragraph81 = document.addParagraph(insurance);
            Paragraph paragraph82 = document.addParagraph("");

            Paragraph paragraph9 = document.addParagraph("7.Учет и возврат продукции");
            document.addParagraph("");
            paragraph9.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph9.setFont(myFont2);
            Paragraph paragraph91 = document.addParagraph(accounting);
            Paragraph paragraph92 = document.addParagraph("");

            Paragraph paragraph10 = document.addParagraph("8.Товарные знаки и упаковка");
            document.addParagraph("");
            paragraph10.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph10.setFont(myFont2);
            Paragraph paragraph111 = document.addParagraph(trademarks);
            Paragraph paragraph112 = document.addParagraph("");


            Paragraph paragraph30 = document.addParagraph("9.Конфиденциальность");
            document.addParagraph("");
            paragraph30.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph30.setFont(myFont2);
            Paragraph paragraph311 = document.addParagraph(confidentiality);
            Paragraph paragraph312 = document.addParagraph("");

            Paragraph paragraph411 = document.addParagraph("10.Срок действия и прекращения");
            document.addParagraph("");
            paragraph411.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph411.setFont(myFont2);
            Paragraph paragraph4111 = document.addParagraph(timeofcontract);
            Paragraph paragraph4112 = document.addParagraph("");

            Paragraph paragraph511 = document.addParagraph("11.Форс-мажор");
            document.addParagraph("");
            paragraph511.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph511.setFont(myFont2);
            Paragraph paragraph5111 = document.addParagraph(forcemajeure);
            Paragraph paragraph5112 = document.addParagraph("");

            Paragraph paragraph611 = document.addParagraph("12.Отказ");
            document.addParagraph("");
            paragraph611.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph611.setFont(myFont2);
            Paragraph paragraph6111 = document.addParagraph(refuce);
            Paragraph paragraph6112 = document.addParagraph("");

            Paragraph paragraph711 = document.addParagraph("13.Полный договор");
            document.addParagraph("");
            paragraph711.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph711.setFont(myFont2);
            Paragraph paragraph7111 = document.addParagraph(fullcontract);
            Paragraph paragraph7112 = document.addParagraph("");

            Paragraph paragraph911 = document.addParagraph("14.Язык");
            document.addParagraph("");
            paragraph911.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph911.setFont(myFont2);
            Paragraph paragraph9111 = document.addParagraph(language);
            Paragraph paragraph9112 = document.addParagraph("");

            Paragraph paragraph1111 = document.addParagraph("15.Право, которое применяется и юрисдикция");
            document.addParagraph("");
            paragraph1111.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph1111.setFont(myFont2);
            Paragraph paragraph1112 = document.addParagraph(jurisdiction);
            Paragraph paragraph1113 = document.addParagraph("");

            Paragraph paragraph1114 = document.addParagraph("15.Другие условия");
            document.addParagraph("");
            paragraph1114.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph1114.setFont(myFont2);
            Paragraph paragraph1115 = document.addParagraph(otherconditions);
            Paragraph paragraph1116 = document.addParagraph("");

            Paragraph paragraph211 = document.addParagraph("16.Реквизиты сторон");
            document.addParagraph("");
            paragraph211.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph211.setFont(myFont2);

            Table table1 = document.addTable(4, 2);
            Cell cell = table1.getCellByPosition(0, 0);
            cell.setStringValue("Исполнитель:");
            Cell cell2 = table1.getCellByPosition(1, 0);
            cell2.setStringValue("Заказчик:");
            Cell cell3 = table1.getCellByPosition(0, 1);
            cell3.setStringValue(requisitesco);
            Cell cell4 = table1.getCellByPosition(1, 1);
            cell4.setStringValue(requisites);
            Cell cell5 = table1.getCellByPosition(0, 2);
            cell5.setStringValue("Директор");
            Cell cell6 = table1.getCellByPosition(1, 2);
            cell6.setStringValue("Директор");
            Cell cell7 = table1.getCellByPosition(0, 3);
            cell7.setStringValue("_______________________");
            Cell cell8 = table1.getCellByPosition(1, 3);
            cell8.setStringValue("_______________________ "+director);

            Border borderbase=new Border(Color.WHITE,2, StyleTypeDefinitions.SupportedLinearMeasure.PT);

            cell.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell2.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell2.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell3.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell3.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell4.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell4.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell5.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell5.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell6.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell6.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell7.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell7.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell8.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell8.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);

            cell.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            cell2.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);

            cell.setFont(myFont3);
            cell2.setFont(myFont3);

            document.addPageBreak();

            for (int i = 0; i < info.tablerows.size(); i++) {
                Paragraph paragraph219= document.addParagraph("Приложение "+((Integer)1+(Integer)i)*1+" к Договору №"+number+" от "+contdate);
                document.addParagraph("");
                paragraph219.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
                paragraph219.setFont(myFont2);
                document.addParagraph("");
                document.addParagraph(info.tablerows.get(i).text);
                Paragraph paragraph1003=document.addParagraph("");
                Paragraph paragraph1001=document.addParagraph("");
                Paragraph paragraph1000=document.addParagraph("");
                document.insertTable(paragraph1000,table1,true);
                document.addPageBreak();
                for (int j = 0; j < info.tablerows.get(i).mediarows.size(); j++) {
                    //document.newImage(java.net.URI.create(info.tablerows.get(i).mediarows.get(j).attachment));
                    document.addParagraph(info.tablerows.get(i).mediarows.get(j).urii);
                    document.addParagraph("");
                    Paragraph paragraph220= document.addParagraph(info.tablerows.get(i).mediarows.get(j).picturename);
                    paragraph220.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
                    Paragraph paragraph1002=document.addParagraph("");
                    document.insertTable(paragraph1002,table1,true);
                    document.addPageBreak();
                }
            }

                document.save("/home/anton/printForms/contracts/contractRSS.odt");
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}