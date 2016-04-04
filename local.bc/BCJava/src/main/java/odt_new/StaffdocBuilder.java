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
public class StaffdocBuilder {
    public static void main(String[] args) {
        try {
            TextDocument document =TextDocument.newTextDocument();

            StaffInfo info =new StaffInfo();
            String enterprise=info.enterprise;
            String number=info.number;
            String date=info.date;
            String employeename=info.employeename;
            String startdate=info.startdate;
            String dateend=info.dateend;
            String department=info.department;
            String post=info.post;
            String dateterm=info.dateterm;
            String jobtype=info.jobtype;
            String actual=info.actual;
            String time=info.time;
            String salary=info.salary;

            String director = info.director;

            Paragraph paragraph00= document.addParagraph("Типова форма № П-1");
            Paragraph paragraph001=document.addParagraph("ЗАТВЕРДЖЕННО");
            Paragraph paragraph002=document.addParagraph("наказом Держкостату України");
            Paragraph paragraph003=document.addParagraph("від 5 грудня 2008 р. №489");

            Font myFont5 = new Font("Arial", StyleTypeDefinitions.FontStyle.REGULAR, 10, Color.BLACK);
            Font myFont4 = new Font("Arial", StyleTypeDefinitions.FontStyle.REGULAR, 8, Color.BLACK);

            paragraph00.setFont(myFont5);
            paragraph001.setFont(myFont5);
            paragraph002.setFont(myFont5);
            paragraph003.setFont(myFont5);

            paragraph00.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.RIGHT);
            paragraph001.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.RIGHT);
            paragraph002.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.RIGHT);
            paragraph003.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.RIGHT);

            Paragraph paragraph01=document.addParagraph(enterprise);
            Paragraph paragraph02=document.addParagraph("Найменування підприємства(установи, організації)");
            paragraph01.setFont(myFont5);
            paragraph02.setFont(myFont4);
            document.addParagraph("");
            document.addParagraph("");

            Paragraph paragraph1 = document.addParagraph("Наказ (Розпорядження) №"+number+" від "+date);
            paragraph1.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Font myFont = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 14, Color.BLACK);
            paragraph1.setFont(myFont);
            Paragraph paragraph11 = document.addParagraph("про прийняття на работу");
            paragraph11.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Font myFont3 = new Font("Arial", StyleTypeDefinitions.FontStyle.REGULAR, 12, Color.BLACK);
            paragraph11.setFont(myFont3);
            Paragraph paragraph12 = document.addParagraph("");
            Paragraph paragraph13=document.addParagraph(employeename);
            Paragraph paragraph131=document.addParagraph("(прізвище,ім'я, по батькові)");
            paragraph13.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph131.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph131.setFont(myFont4);
            document.addParagraph("");

            Table table1 = document.addTable(2,2);
            Cell cell1 = table1.getCellByPosition(0, 0);
            cell1.setStringValue("Прийняти на роботy з: "+startdate+"                                                               ");
            Cell cell2 = table1.getCellByPosition(0, 1);
            cell2.setStringValue("                                до: "+dateend+"                                                             ");
            Cell cell3 = table1.getCellByPosition(1, 0);
            cell3.setStringValue("Табельний номер");
            Cell cell4 = table1.getCellByPosition(1, 1);
            cell4.setStringValue("0000000_");

            cell3.setFont(myFont5);
            cell4.setFont(myFont5);

            Paragraph paragraph16 = document.addParagraph("(Заповнюється у разі строкового трудового договору(контракту))");

            Border borderbase=new Border(Color.WHITE,2, StyleTypeDefinitions.SupportedLinearMeasure.PT);
            table1.getCellByPosition(0, 0).setBorders(StyleTypeDefinitions.CellBordersType.RIGHT, borderbase);
            table1.getCellByPosition(0, 1).setBorders(StyleTypeDefinitions.CellBordersType.RIGHT, borderbase);
            table1.getCellByPosition(0, 0).setBorders(StyleTypeDefinitions.CellBordersType.LEFT, borderbase);
            table1.getCellByPosition(0, 1).setBorders(StyleTypeDefinitions.CellBordersType.LEFT, borderbase);
            table1.getCellByPosition(0, 0).setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            table1.getCellByPosition(0, 1).setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);

            cell1.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.LEFT);
            cell2.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.LEFT);
            table1.getCellByPosition(1, 0).setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            table1.getCellByPosition(1, 1).setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);

            table1.getColumnByIndex(0).setWidth(120);
            table1.getColumnByIndex(1).setWidth(40);

            System.out.println(table1.getColumnByIndex(0).getWidth());
            System.out.println(table1.getColumnByIndex(1).getWidth());
            System.out.println(table1.getColumnByIndex(1).getWidth());
            table1.getColumnByIndex(1).isOptimalWidth();
            System.out.println(table1.getColumnByIndex(1).getWidth());

            table1.getCellByPosition(0,0).setFont(myFont5);
            table1.getCellByPosition(0,1).setFont(myFont5);

            paragraph16.setFont(myFont4);
            document.addParagraph("");

            Paragraph paragraph17=document.addParagraph(department);
            paragraph17.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Paragraph paragraph171=document.addParagraph("(Назва структурного підрозділу)");
            paragraph171.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph171.setFont(myFont4);

            Paragraph paragraph18=document.addParagraph(post);
            paragraph18.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Paragraph paragraph181=document.addParagraph("(Назва професії(посади), кваліфікації)");
            paragraph181.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph181.setFont(myFont4);

            document.addParagraph("");
            document.addParagraph("");

            Table table2 = document.addTable(11,2);
            Cell cell5 = table2.getCellByPosition(0, 0);
            cell5.setStringValue("Умови прийняття на роботу");
            Cell cell6 = table2.getCellByPosition(0, 1);
            cell6.setStringValue("(необхідне відмітити позначкою \"x\")");
            Cell cell7 = table2.getCellByPosition(1, 0);
            cell7.setStringValue("Умови роботи");
            Cell cell8 = table2.getCellByPosition(1, 1);
            cell8.setStringValue("(необхідне відмітити позначкою \"x\")");
            Cell cell9 = table2.getCellByPosition(0, 2);
            cell9.setStringValue("☐ на конкурсній основі");
            Cell cell10 = table2.getCellByPosition(1,2 );

            if (actual=="mainplace") {
                cell10.setStringValue("робота:  ☒ основна ☐ за сумісництвом");
            }
            else {
                cell10.setStringValue("робота:  ☐ основна ☒ за сумісництвом");
            }

            Cell cell11 = table2.getCellByPosition(0, 3);
            cell11.setStringValue("☐ за умовами контракту до "+dateend);
            Cell cell12 = table2.getCellByPosition(0, 4);
            cell12.setStringValue(" у разі необхідності вказати дату (дд.мм.рррр.)");
            Cell cell13 = table2.getCellByPosition(0, 5);
            cell13.setStringValue("☐ зі строком випробування "+dateterm);
            Cell cell15 = table2.getCellByPosition(0, 6);
            cell15.setStringValue("☐ на час виконання певної роботи");
            Cell cell16 = table2.getCellByPosition(0, 7);
            cell16.setStringValue("☐ на період відсутності основного працівника");
            Cell cell17 = table2.getCellByPosition(0, 8);
            cell17.setStringValue("☐ із кадрового резерву");
            Cell cell18 = table2.getCellByPosition(0, 9);
            cell18.setStringValue("☐ за результатами успішного стажування");
            Cell cell19 = table2.getCellByPosition(0, 10);
            cell19.setStringValue("☐ переведення");
            Cell cell21 = table2.getCellByPosition(1,3 );
            cell21.setStringValue(" умови праці (згідно атестації робочого місця):");
            Cell cell22 = table2.getCellByPosition(1,4);
            cell22.setStringValue("_____________________________________");
            Cell cell23 = table2.getCellByPosition(1,5);
            cell23.setStringValue("_____________________________________");
            Cell cell24 = table2.getCellByPosition(1,6);
            cell24.setStringValue("Тривалість робочого тижня "+time);
            Cell cell25 = table2.getCellByPosition(1,7);
            cell25.setStringValue("Тривалість робочого тижня тривалість робочого дня (тижня) при роботі з неповним робочим часом __________");
            Cell cell26 = table2.getCellByPosition(1,8);
            cell26.setStringValue("_____________________________________");
            Cell cell27 = table2.getCellByPosition(1,9);
            cell27.setStringValue("_____________________________________");
            Cell cell28 = table2.getCellByPosition(1,10);
            cell28.setStringValue("_____________________________________");

            cell12.setFont(myFont4);

            switch (jobtype) {
                case "value1": cell9.setStringValue("☒ на конкурсній основі");
                    break;
                case "value2": cell11.setStringValue("☒ за умовами контракту до "+dateend);
                    break;
                case "value3": cell13.setStringValue("☒ зі строком випробування "+dateterm);
                    break;
                case "value4": cell15.setStringValue("☒ на час виконання певної роботи");
                    break;
                case "value5": cell16.setStringValue("☒ на період відсутності основного працівника");
                    break;
                case "value6": cell17.setStringValue("☒ із кадрового резерву");
                    break;
                case "value7": cell18.setStringValue("☒ за результатами успішного стажування");
                    break;
                case "value8": cell19.setStringValue("☒ переведення");
                    break;
            }

            cell5.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            cell7.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            cell6.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            cell8.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);

            Font myFont6 = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 10, Color.BLACK);
            cell5.setFont(myFont6);
            cell7.setFont(myFont6);
            cell6.setFont(myFont4);
            cell8.setFont(myFont4);

            table2.getCellByPosition(0, 0).setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            table2.getCellByPosition(0, 1).setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            table2.getCellByPosition(0, 0).setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            table2.getCellByPosition(0, 1).setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell7.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell7.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell8.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell8.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell9.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell9.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell10.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell10.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell11.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell11.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell12.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell12.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell13.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell13.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell15.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell15.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell16.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell16.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell17.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell17.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell18.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell18.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell19.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell19.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell21.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell21.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell22.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell22.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell23.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell23.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell24.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell24.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell25.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell25.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell26.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell26.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell27.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell27.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell28.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell28.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);

            Font myFont7 = new Font("Arial", StyleTypeDefinitions.FontStyle.REGULAR, 10, Color.BLACK);

            cell9.setFont(myFont7);
            cell10.setFont(myFont7);
            cell11.setFont(myFont7);
            cell13.setFont(myFont7);
            cell15.setFont(myFont7);
            cell16.setFont(myFont7);
            cell17.setFont(myFont7);
            cell18.setFont(myFont7);
            cell19.setFont(myFont7);
            cell21.setFont(myFont7);
            cell22.setFont(myFont7);
            cell23.setFont(myFont7);
            cell24.setFont(myFont7);
            cell25.setFont(myFont7);
            cell26.setFont(myFont7);
            cell27.setFont(myFont7);
            cell28.setFont(myFont7);

            Paragraph paragraph25=document.addParagraph("Оклад (тарифна ставка): "+salary);
            document.addParagraph("");
            paragraph25.setFont(myFont5);

            Table table3 = document.addTable(2,2);
            Cell cell30 = table3.getCellByPosition(0, 0);
            cell30.setStringValue("надбавка за _________________☐☐☐ % ");
            Cell cell31 = table3.getCellByPosition(0, 1);
            cell31.setStringValue("надбавка за _________________☐☐☐ % ");
            Cell cell32 = table3.getCellByPosition(1, 0);
            cell32.setStringValue("надбавка за _________________☐☐☐ %");
            Cell cell33 = table3.getCellByPosition(1, 1);
            cell33.setStringValue("надбавка за __________________☐☐☐ %");
            document.addParagraph("");
            Paragraph paragraph34=document.addParagraph("Доплата ☐☐☐☐☐ грн. ☐☐ коп.");
            document.addParagraph("");

            paragraph34.setFont(myFont5);
            paragraph25.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph34.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);

            Paragraph paragraph26=document.addParagraph("Директор підприєства"+"     "+"_____________________"+"                                 "+director);
            Paragraph paragraph27=document.addParagraph("                                                                                "+"(підпис)"+"                                                "+"(Прізвище, ім'я, по батькові)");
            paragraph27.setFont(myFont4);
            paragraph26.setFont(myFont5);
            Paragraph paragraph28=document.addParagraph("З наказом ознайомлений"+"     "+"_____________________"+"      "+" \"____\"_________20___года");
            Paragraph paragraph29=document.addParagraph("                                                                 "+"(підпис працівника)");
            paragraph29.setFont(myFont4);
            paragraph28.setFont(myFont5);

            document.save("/home/anton/printForms/staffdoc.odt");

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
