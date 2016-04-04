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
public class ContractBuilderTMC {
    public static void main(String[] args) {
        try {
            TextDocument document = TextDocument.newTextDocument();

            //Contract TMC

            ContractInfoTMC info = new ContractInfoTMC();
            String number = info.number;
            String place = info.place;
            String contdate = info.contdate;
            String introduction = info.introduction;
            String contractsubject = info.contractsubject;
            String orderofworksexecution = info.orderofworksexecution;
            String rights = info.rights;
            String termsofpayment = info.termsofpayment;
            String liabilities = info.liabilities;
            String changesofcntract = info.changesofcntract;
            String specialconditions = info.specialconditions;
            String termsofcontract = info.termsofcontract;
            String requisites = info.requisites;
            String requisitesco = info.requisitescont;
            String director = info.director;


            ContractInfoTMC.ApplicationRow row1 = new ContractInfoTMC.ApplicationRow();
            row1.text = "В данном приложении прилагаются блок-схемы";

            ContractInfoTMC.ApplicationRow.MediaRow subrow1=new ContractInfoTMC.ApplicationRow.MediaRow();
            subrow1.urii=("file:////home/anton/1.png");
            subrow1.picturename="Graphic1";
            row1.mediarows.add(subrow1);

            ContractInfoTMC.ApplicationRow.MediaRow subrow2=new ContractInfoTMC.ApplicationRow.MediaRow();
            subrow2.urii=("file:///home/anton/1.png");
            subrow2.picturename="Graphic2";
            row1.mediarows.add(subrow2);

            ContractInfoTMC.ApplicationRow.MediaRow subrow3=new ContractInfoTMC.ApplicationRow.MediaRow();
            subrow3.urii=("file:///home/anton/1.png");
            subrow3.picturename="Graphic3";
            row1.mediarows.add(subrow3);

            info.tablerows.add(row1);

            ContractInfoTMC.ApplicationRow row2 = new ContractInfoTMC.ApplicationRow();
            row2.text = "В данном приложении прилагаются важные детали";
            info.tablerows.add(row2);


            Paragraph paragraph1 = document.addParagraph("Договор №" + number);
            paragraph1.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Font myFont = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 14, Color.BLACK);
            paragraph1.setFont(myFont);
            Paragraph paragraph11 = document.addParagraph("на техническое обслуживание холодильников, кондиционеров");
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

            Paragraph paragraph4 = document.addParagraph("2.Обязанности исполнителя");
            document.addParagraph("");
            paragraph4.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph4.setFont(myFont2);
            Paragraph paragraph41 = document.addParagraph(orderofworksexecution);
            Paragraph paragraph42 = document.addParagraph("");

            Paragraph paragraph5 = document.addParagraph("3.Права заказчика");
            document.addParagraph("");
            paragraph5.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph5.setFont(myFont2);
            Paragraph paragraph51 = document.addParagraph(rights);
            Paragraph paragraph52 = document.addParagraph("");

            Paragraph paragraph6 = document.addParagraph("4.Условия оплаты");
            document.addParagraph("");
            paragraph6.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph6.setFont(myFont2);
            Paragraph paragraph61 = document.addParagraph(termsofpayment);
            Paragraph paragraph62 = document.addParagraph("");

            Paragraph paragraph7 = document.addParagraph("5.Ответственность сторон");
            document.addParagraph("");
            paragraph7.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph7.setFont(myFont2);
            Paragraph paragraph71 = document.addParagraph(liabilities);
            Paragraph paragraph72 = document.addParagraph("");

            Paragraph paragraph8 = document.addParagraph("6.Условия расторжения или изменения договора");
            document.addParagraph("");
            paragraph8.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph8.setFont(myFont2);
            Paragraph paragraph81 = document.addParagraph(changesofcntract);
            Paragraph paragraph82 = document.addParagraph("");

            Paragraph paragraph9 = document.addParagraph("7.Особые условия");
            document.addParagraph("");
            paragraph9.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph9.setFont(myFont2);
            Paragraph paragraph91 = document.addParagraph(specialconditions);
            Paragraph paragraph92 = document.addParagraph("");

            Paragraph paragraph10 = document.addParagraph("8.Срок действия договора");
            document.addParagraph("");
            paragraph10.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph10.setFont(myFont2);
            Paragraph paragraph111 = document.addParagraph(termsofcontract);
            Paragraph paragraph112 = document.addParagraph("");

            Paragraph paragraph211 = document.addParagraph("9.Реквизиты сторон");
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

            document.addParagraph("");

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
                document.save("/home/anton/printForms/contracts/contractTMC.odt");
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
