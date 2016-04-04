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
 * Created by Iryna on 11.01.2016.
 */
public class MasterPlanBuilder {
    public static void main(String[] args) {
        try {

            MasterPlanInfo masterInfo=new MasterPlanInfo();
            String initialdate=masterInfo.initialdate;
            String lastdate=masterInfo.lastdate;
            String period=masterInfo.period;
            String policy=masterInfo.policy;

            MasterPlanInfo.TableMasterRow row1 = new MasterPlanInfo.TableMasterRow();
            row1.businessobject= "Объект 1";
            row1.programm="Программа1";
            row1.valdate="31/12/2015";
            masterInfo.tablerows.add(row1);

            MasterPlanInfo.TableMasterRow row2 = new MasterPlanInfo.TableMasterRow();
            row2.businessobject= "Объект 2";
            row2.programm="Программа2";
            row2.valdate="31/12/2016";
            masterInfo.tablerows.add(row2);

            MasterPlanInfo.TableMasterRow row3 = new MasterPlanInfo.TableMasterRow();
            row3.businessobject= "Объект 3";
            row3.programm="Программа 3";
            row3.valdate="31/12/2017";
            masterInfo.tablerows.add(row3);


            TextDocument document = TextDocument.newTextDocument();

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


            Header docHeader = document.getHeader();
            Table table = docHeader.addTable(4, 4);

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

            Paragraph paragraph1 = document.addParagraph("Дата вступления в силу: " + initialdate);
            Paragraph paragraph2 = document.addParagraph("Дата последнего пересмотра: " + lastdate);
            Paragraph paragraph3 = document.addParagraph("Период действия: " + period);
            Paragraph paragraph31 = document.addParagraph("");
            Paragraph paragraph32 = document.addParagraph("");

            Paragraph paragraph4 = document.addParagraph("Политика в области валидации");
            paragraph4.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Font myFont = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 14, Color.BLACK);
            paragraph4.setFont(myFont);
            Paragraph paragraph41 = document.addParagraph("");

            Paragraph paragraph5=document.addParagraph(policy);
            Paragraph paragraph51=document.addParagraph("");

            Paragraph paragraph6 = document.addParagraph("Календарный план валидации");
            paragraph6.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            paragraph6.setFont(myFont);
            Paragraph paragraph61 = document.addParagraph("");

            int numrows=masterInfo.tablerows.size();

            Table table1 = document.addTable(1+numrows, 3);
            Cell cell1 = table1.getCellByPosition(0, 0);
            cell1.setStringValue("Объект");
            Cell cell2 = table1.getCellByPosition(1, 0);
            cell2.setStringValue("Программа валидации");
            Cell cell3 = table1.getCellByPosition(2, 0);
            cell3.setStringValue("Дата");

            for(int i=0; i<masterInfo.tablerows.size(); i++) {
                table1.getCellByPosition(0, i+1).setStringValue(i+1+"."+" "+masterInfo.tablerows.get(i).businessobject);
                table1.getCellByPosition(1, i+1).setStringValue(masterInfo.tablerows.get(i).programm);
                table1.getCellByPosition(2, i+1).setStringValue(masterInfo.tablerows.get(i).valdate);
            }

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



            document.save("/home/anton/printForms/MasterPlan.odt");
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}