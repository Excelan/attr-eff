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
public class ContractBuilderBW {
    public static void main(String[] args) {
        try {
            TextDocument document = TextDocument.newTextDocument();

            //Contract TMC

            ContractInfoBW info = new ContractInfoBW();
            String number = info.number;
            String place = info.place;
            String contdate = info.contdate;
            String introduction = info.introduction;
            String contractsubject = info.contractsubject;

            String rightsandliabilities=info.rightsandliabilities;
            String timeofworks=info.timeofworks;
            String termofcustompayments=info.termofcustompayments;
            String payments=info.payments;
            String specialconditions=info.specialconditions;
            String otherconditions=info.otherconditions;

            String requisites = info.requisites;
            String requisitesco = info.requisitescont;
            String director = info.director;


            ContractInfoBW.ApplicationRow row1 = new ContractInfoBW.ApplicationRow();
            row1.text = "В данном приложении прилагаются блок-схемы";

            ContractInfoBW.ApplicationRow.MediaRow subrow1=new ContractInfoBW.ApplicationRow.MediaRow();
            subrow1.attachment =("file:///~odf.png");
            subrow1.text ="Graphic1";
            row1.mediarows.add(subrow1);

            ContractInfoBW.ApplicationRow.MediaRow subrow2=new ContractInfoBW.ApplicationRow.MediaRow();
            subrow2.attachment =("file:///~odf.png");
            subrow2.text ="Graphic2";
            row1.mediarows.add(subrow2);

            ContractInfoBW.ApplicationRow.MediaRow subrow3=new ContractInfoBW.ApplicationRow.MediaRow();
            subrow3.attachment =("file:///~odf.png");
            subrow3.text ="Graphic3";
            row1.mediarows.add(subrow3);

            info.tablerows.add(row1);

            ContractInfoBW.ApplicationRow row2 = new ContractInfoBW.ApplicationRow();
            row2.text = "В данном приложении прилагаются важные детали";
            info.tablerows.add(row2);


            Paragraph paragraph1 = document.addParagraph("Договор №" + number);
            paragraph1.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Font myFont = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 14, Color.BLACK);
            paragraph1.setFont(myFont);
            Paragraph paragraph11 = document.addParagraph("на оказание услуг ТЛС и СТЗ");
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

            Paragraph paragraph3 = document.addParagraph("1.Предмет договора");
            document.addParagraph("");
            paragraph3.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph3.setFont(myFont2);
            Paragraph paragraph31 = document.addParagraph(contractsubject);
            Paragraph paragraph32 = document.addParagraph("");

            Paragraph paragraph4 = document.addParagraph("2.Права и обязанности сторон");
            document.addParagraph("");
            paragraph4.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph4.setFont(myFont2);
            Paragraph paragraph41 = document.addParagraph(rightsandliabilities);
            Paragraph paragraph42 = document.addParagraph("");

            Paragraph paragraph5 = document.addParagraph("3.Срок выполнения работ");
            document.addParagraph("");
            paragraph5.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph5.setFont(myFont2);
            Paragraph paragraph51 = document.addParagraph(timeofworks);
            Paragraph paragraph52 = document.addParagraph("");

            Paragraph paragraph6 = document.addParagraph("4.Условия оплаты таможенных платежей");
            document.addParagraph("");
            paragraph6.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph6.setFont(myFont2);
            Paragraph paragraph61 = document.addParagraph(termofcustompayments);
            Paragraph paragraph62 = document.addParagraph("");

            Paragraph paragraph7 = document.addParagraph("5.Расчеты сторон");
            document.addParagraph("");
            paragraph7.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph7.setFont(myFont2);
            Paragraph paragraph71 = document.addParagraph(payments);
            Paragraph paragraph72 = document.addParagraph("");

            Paragraph paragraph8 = document.addParagraph("5.Особенные условия и ответственность сторон");
            document.addParagraph("");
            paragraph8.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph8.setFont(myFont2);
            Paragraph paragraph81 = document.addParagraph(specialconditions);
            Paragraph paragraph82 = document.addParagraph("");

            Paragraph paragraph9 = document.addParagraph("6.Другие условия");
            document.addParagraph("");
            paragraph9.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph9.setFont(myFont2);
            Paragraph paragraph91 = document.addParagraph(otherconditions);
            Paragraph paragraph92 = document.addParagraph("");


            Paragraph paragraph211 = document.addParagraph("6.Реквизиты сторон");
            document.addParagraph("");
            paragraph211.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph211.setFont(myFont2);

            Table table1 = document.addTable(4, 2);
            Cell cell = table1.getCellByPosition(0, 0);
            cell.setStringValue("Исполнитель:");
            Cell cell2 = table1.getCellByPosition(1, 0);
            cell2.setStringValue("Заказчик:");
            Cell cell3 = table1.getCellByPosition(0, 1);
            cell3.setStringValue(requisites);
            Cell cell4 = table1.getCellByPosition(1, 1);
            cell4.setStringValue(requisitesco);
            Cell cell5 = table1.getCellByPosition(0, 2);
            cell5.setStringValue("Директор");
            Cell cell6 = table1.getCellByPosition(1, 2);
            cell6.setStringValue("Директор");
            Cell cell7 = table1.getCellByPosition(0, 3);
            cell7.setStringValue("_______________________ "+director);
            Cell cell8 = table1.getCellByPosition(1, 3);
            cell8.setStringValue("_______________________");

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
                    document.addParagraph(info.tablerows.get(i).mediarows.get(j).attachment);
                    document.addParagraph("");
                    Paragraph paragraph220= document.addParagraph(info.tablerows.get(i).mediarows.get(j).text);
                    paragraph220.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
                    Paragraph paragraph1002=document.addParagraph("");
                    document.insertTable(paragraph1002,table1,true);
                    document.addPageBreak();
                }
            }
                document.save("/home/anton/printForms/contracts/contractBW.odt");
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
