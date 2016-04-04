package odt_new;

/**
 * Created by anton on 21.01.16.
 */

import org.odftoolkit.odfdom.type.Color;
import org.odftoolkit.simple.TextDocument;
import org.odftoolkit.simple.style.Border;
import org.odftoolkit.simple.style.Font;
import org.odftoolkit.simple.style.StyleTypeDefinitions;
import org.odftoolkit.simple.table.Cell;
import org.odftoolkit.simple.table.Table;
import org.odftoolkit.simple.text.Paragraph;

import java.nio.charset.Charset;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.Statement;


public class Loader {
    private static final String url = "jdbc:postgresql://127.0.0.1/bc";
    private static final String user = "bc";
    private static final String password = "FFpS8ZBy3";

    public String introduction;


    public static void main(String args[]) {
        Connection c = null;
        Statement stmt = null;
        try {
            Class.forName("org.postgresql.Driver");
            c = DriverManager.getConnection(url, user, password);
            c.setAutoCommit(false);
            System.out.println("Opened database successfully");
            System.out.println("");


            stmt = c.createStatement();
            ResultSet rs = stmt.executeQuery("SELECT place, date, introduction, contractsubject, rightsandliabilities, timeofworks, termofcustompayments,payments,specialconditions, otherconditions FROM \"Document_Contract_BW\" WHERE id=326632251 ;");
            while (rs.next()) {

                //403235077
                //1672101873 - Media atributed
                //532769744 - Media attributed

                String place = rs.getString("place");
                String contdate = rs.getString("date");
                String introduction = rs.getString("introduction");
                String contractsubject = rs.getString("contractsubject");
                String rightsandliabilities = rs.getString("rightsandliabilities");
                String timeofworks = rs.getString("timeofworks");
                String termofcustompayments = rs.getString("termofcustompayments");
                String payments = rs.getString("payments");
                String specialconditions = rs.getString("specialconditions");
                String otherconditions = rs.getString("otherconditions");

             //   String introduction = StringEscapeUtils.unescapeHtml4(introd);
                //     .replace("&nbsp;", "\u00A0")
                //     .replace("&amp;", "&");
                Charset.forName("UTF-8").encode(introduction);

                System.out.println("Вступление " + introduction);
                System.out.println("Предмет договора" + contractsubject);
                System.out.println("Права и обязанности " + rightsandliabilities);
                System.out.println("Время работ" + timeofworks);

                rs = stmt.executeQuery("SELECT title,legaladdress, ba, mfo, edropou FROM \"Company_LegalEntity_Counterparty\" WHERE id=115997801");
                while (rs.next()) {
                    String requisitesco = rs.getString("title") + "\n " + rs.getString("legaladdress") + "\n" + "Банковский счет: " + rs.getString("ba") + "\n" + "МФО: " + rs.getString("mfo") + "\n" + "ЕДРОПОУ: " + rs.getString("edropou");
                    rs = stmt.executeQuery("SELECT text FROM  \"Document_ContractApplication_Universal\" WHERE id=403235077");
                    while (rs.next()) {
                        String text = rs.getString("text");
                        System.out.println("Это приложение к договору" + text);
                        rs = stmt.executeQuery("SELECT text, attachment WHERE id=532769744");
                        while (rs.next()) {
                           String text2=rs.getString("text");
                            String attachment=rs.getString("attachment");
                            System.out.println("Это приложение к картинке "+text2);
                            System.out.println("Это ссылка на картинку "+text2);


                            try {
                            TextDocument document = TextDocument.newTextDocument();

                            ContractInfoBW info = new ContractInfoBW();
                            String number = info.number;
                            // String place = info.place;
                            // String contdate = info.contdate;
                            // String introduction = info.introduction;
                            // String contractsubject = info.contractsubject;

                            // String rightsandliabilities=info.rightsandliabilities;
                            // String timeofworks=info.timeofworks;
                            //  String termofcustompayments=info.termofcustompayments;
                            //  String payments=info.payments;
                            //  String specialconditions=info.specialconditions;
                            //  String otherconditions=info.otherconditions;

                            String requisites = info.requisites;
                            // String requisitesco = info.requisitescont;
                            String director = info.director;


                            ContractInfoBW.ApplicationRow row1 = new ContractInfoBW.ApplicationRow();
                            row1.text = "В данном приложении прилагаются блок-схемы";

                            ContractInfoBW.ApplicationRow.MediaRow subrow1 = new ContractInfoBW.ApplicationRow.MediaRow();
                            subrow1.attachment = ("file:///~odf.png");
                            subrow1.text = "Graphic1";
                            row1.mediarows.add(subrow1);

                            ContractInfoBW.ApplicationRow.MediaRow subrow2 = new ContractInfoBW.ApplicationRow.MediaRow();
                            subrow2.attachment = ("file:///~odf.png");
                            subrow2.text = "Graphic2";
                            row1.mediarows.add(subrow2);

                            ContractInfoBW.ApplicationRow.MediaRow subrow3 = new ContractInfoBW.ApplicationRow.MediaRow();
                            subrow3.attachment = ("file:///~odf.png");
                            subrow3.text = "Graphic3";
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
                            cell7.setStringValue("_______________________ " + director);
                            Cell cell8 = table1.getCellByPosition(1, 3);
                            cell8.setStringValue("_______________________");

                            Border borderbase = new Border(Color.WHITE, 2, StyleTypeDefinitions.SupportedLinearMeasure.PT);

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
                                Paragraph paragraph219 = document.addParagraph("Приложение " + ((Integer) 1 + (Integer) i) * 1 + " к Договору №" + number + " от " + contdate);
                                document.addParagraph("");
                                paragraph219.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
                                paragraph219.setFont(myFont2);
                                document.addParagraph("");
                                document.addParagraph(info.tablerows.get(i).text);
                                Paragraph paragraph1003 = document.addParagraph("");
                                Paragraph paragraph1001 = document.addParagraph("");
                                Paragraph paragraph1000 = document.addParagraph("");
                                document.insertTable(paragraph1000, table1, true);
                                document.addPageBreak();
                                for (int j = 0; j < info.tablerows.get(i).mediarows.size(); j++) {
                                    //document.newImage(java.net.URI.create(info.tablerows.get(i).mediarows.get(j).attachment));
                                    document.addParagraph(info.tablerows.get(i).mediarows.get(j).attachment);
                                    document.addParagraph("");
                                    Paragraph paragraph220 = document.addParagraph(info.tablerows.get(i).mediarows.get(j).text);
                                    paragraph220.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
                                    Paragraph paragraph1002 = document.addParagraph("");
                                    document.insertTable(paragraph1002, table1, true);
                                    document.addPageBreak();
                                }
                            }
                            document.save("/home/anton/printForms/contracts/contractBW.odt");
                        } catch (Exception e) {
                            e.printStackTrace();
                        }
                    }
                }
            }
        }

                rs.close();
                stmt.close();
                c.close();
            }catch(Exception e) {
            System.err.println(e.getClass().getName() + ": " + e.getMessage());
            System.exit(0);

            }
            System.out.println("Operation done successfully");
        }
    }


