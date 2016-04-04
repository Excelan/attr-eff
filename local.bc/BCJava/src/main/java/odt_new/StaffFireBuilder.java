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
public class StaffFireBuilder {
    public static void main(String[] args) {
        try {
            TextDocument document =TextDocument.newTextDocument();

            StaffFireInfo info =new StaffFireInfo();
            String enterprise=info.enterprise;
            String number=info.number;
            String date=info.date;
            String datefire=info.datefire;
            String employeename=info.employeename;
            String base=info.base;
            String department=info.department;
            String post=info.post;
            String reason=info.reason;
            String severancepay=info.severancepay;
            String dateunusedvacation=info.dateunusedvacation;

            String director = info.director;

            Paragraph paragraph00= document.addParagraph("Типова форма № П-4");
            Paragraph paragraph001=document.addParagraph("ЗАТВЕРДЖЕННО");
            Paragraph paragraph002=document.addParagraph("наказом Держкостату України");
            Paragraph paragraph003=document.addParagraph("від 5 грудня 2008 р. N 489");

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

            Font myFont6 = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 10, Color.BLACK);

            Paragraph paragraph01=document.addParagraph(enterprise);
            Paragraph paragraph02=document.addParagraph("Найменування підприємства(установи, організації)");
            paragraph01.setFont(myFont6);
            paragraph02.setFont(myFont4);

            Table table5 = document.addTable(2,3);
            Cell cell99 = table5.getCellByPosition(0, 0);
            cell99.setStringValue("                      ");
            Cell cell98 = table5.getCellByPosition(0, 1);
            cell98.setStringValue("                      ");
            Cell cell97 = table5.getCellByPosition(1, 0);
            cell97.setStringValue("ЄДРПОУ");
            Cell cell96 = table5.getCellByPosition(1, 1);
            cell96.setStringValue("Дата складання");
            Cell cell95 = table5.getCellByPosition(2, 0);
            cell95.setStringValue("21672519");
            Cell cell94 = table5.getCellByPosition(2,1 );
            cell94.setStringValue(date);
            document.addParagraph("");

           table5.getColumnByIndex(0).setWidth(110);
            table5.getColumnByIndex(1).setWidth(70);
            table5.getColumnByIndex(2).setWidth(35);
            cell97.setFont(myFont5);
            cell96.setFont(myFont5);
            cell95.setFont(myFont5);
            cell94.setFont(myFont5);

            Border borderbase=new Border(Color.WHITE,2, StyleTypeDefinitions.SupportedLinearMeasure.PT);

            cell99.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell99.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            cell98.setBorders(StyleTypeDefinitions.CellBordersType.LEFT_RIGHT, borderbase);
            cell98.setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);

            Paragraph paragraph1 = document.addParagraph("Наказ № "+number);
            Paragraph paragraph111 = document.addParagraph("(Розпорядження)");
            Paragraph paragraph112 = document.addParagraph("про припинення трудового договору (контракту)");
            paragraph1.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph111.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph112.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);

            Font myFont = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 14, Color.BLACK);
            paragraph1.setFont(myFont);
            paragraph111.setFont(myFont6);
            paragraph112.setFont(myFont6);

            document.addParagraph(" ");
            document.addParagraph("Звільнити: "+datefire);

            Table table1 = document.addTable(2,2);
            Cell cell1 = table1.getCellByPosition(0, 0);
            cell1.setStringValue("                                                                                            ");
            Cell cell2 = table1.getCellByPosition(0, 1);
            cell2.setStringValue("                                                                                             ");
            Cell cell3 = table1.getCellByPosition(1, 0);
            cell3.setStringValue("Табельний номер");
            Cell cell4 = table1.getCellByPosition(1, 1);
            cell4.setStringValue("0000000__");

            cell3.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            cell4.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            cell3.setFont(myFont5);
            cell4.setFont(myFont5);

            table1.getColumnByIndex(0).setWidth(130);

            table1.getCellByPosition(0, 0).setBorders(StyleTypeDefinitions.CellBordersType.RIGHT, borderbase);
            table1.getCellByPosition(0, 1).setBorders(StyleTypeDefinitions.CellBordersType.RIGHT, borderbase);
            table1.getCellByPosition(0, 0).setBorders(StyleTypeDefinitions.CellBordersType.LEFT, borderbase);
            table1.getCellByPosition(0, 1).setBorders(StyleTypeDefinitions.CellBordersType.LEFT, borderbase);
            table1.getCellByPosition(0, 0).setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);
            table1.getCellByPosition(0, 1).setBorders(StyleTypeDefinitions.CellBordersType.TOP_BOTTOM, borderbase);

            Paragraph paragraph12 = document.addParagraph("");
            Paragraph paragraph13=document.addParagraph(employeename);
            Paragraph paragraph131=document.addParagraph("(прізвище,ім'я, по батькові)");
            paragraph13.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph131.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph131.setFont(myFont4);
            document.addParagraph("");

            Paragraph paragraph17=document.addParagraph(department);
            paragraph17.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Paragraph paragraph171=document.addParagraph("(назва структурного підрозділу)");
            paragraph171.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph171.setFont(myFont4);

            Paragraph paragraph18=document.addParagraph(post);
            paragraph18.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Paragraph paragraph181=document.addParagraph("(назва професії (посади), розряд, клас (категорія) кваліфікації)");
            paragraph181.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph181.setFont(myFont4);

            Paragraph paragraph19=document.addParagraph(reason);
            paragraph19.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Paragraph paragraph191=document.addParagraph("(причина звільнення)");
            paragraph191.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph191.setFont(myFont4);

            Paragraph paragraph20=document.addParagraph(base);
            paragraph20.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Paragraph paragraph201=document.addParagraph("(підстави звільнення)");
            paragraph201.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph201.setFont(myFont4);

            for (int i=0;i<3;i++) {
                document.addParagraph("");
            }

            Paragraph paragraph210=document.addParagraph("☐ Вихідна допомога "+severancepay);

            document.addParagraph("");
            document.addParagraph("");
            document.addParagraph("");

            Font myFont7 = new Font("Arial", StyleTypeDefinitions.FontStyle.REGULAR, 10, Color.BLACK);

            for (int i=0;i<7;i++) {
                document.addParagraph("");
            }

            Paragraph paragraph26=document.addParagraph("Керівник підприєства"+"                      "+"_______________________"+"                                 "+director);
            Paragraph paragraph27=document.addParagraph("                                                                                             "+"(підпис)"+"                                                   "+"(Прізвище, ім'я, по батькові)");
            paragraph27.setFont(myFont4);
            paragraph26.setFont(myFont5);
            document.addParagraph("");
            Paragraph paragraph28=document.addParagraph("З наказом ознайомлений"+"     "+"_____________________"+"                                  "+" \"____\"_________20___года");
            Paragraph paragraph29=document.addParagraph("                                                                 "+"(підпис працівника)");
            paragraph29.setFont(myFont4);
            paragraph28.setFont(myFont5);

            document.addParagraph("");
            document.addParagraph("");
            document.addParagraph("");

            Paragraph paragraph311=document.addParagraph("Нарахована компенсація за невикористану щорічну основну відпустку в кількості "+dateunusedvacation+" днів");

            document.save("/home/anton/printForms/stafffiredoc.odt");

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
