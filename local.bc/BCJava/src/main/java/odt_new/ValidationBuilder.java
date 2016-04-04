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
public class ValidationBuilder {
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
            discuss.add("Lavrov");
            discuss.add("Lartsev");
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

            ValidationInfo info =new ValidationInfo();
            String program=info.programm;


            ValidationInfo.StructMaterialRow row1 = new ValidationInfo.StructMaterialRow();
            row1.businessobject= "Объект 1";
            row1.numberequipment="3";
            row1.specification="Характеристика 1";
            info.tablerows1.add(row1);

            ValidationInfo.StructMaterialRow row2 = new ValidationInfo.StructMaterialRow();
            row2.businessobject= "Объект 2";
            row2.numberequipment="4";
            row2.specification="Характеристика 2";
            info.tablerows1.add(row2);

            ValidationInfo.StructParametrRow  row3 = new ValidationInfo.StructParametrRow ();
            row3.titleparametr="Название параметра 1";
            row3.descriptionmethodic="Описание параметра 1";
            info.tablerows2.add(row3);

            ValidationInfo.StructParametrRow  row4 = new ValidationInfo.StructParametrRow ();
            row4.titleparametr="Название параметра 2";
            row4.descriptionmethodic="Описание параметра 2";
            info.tablerows2.add(row4);


            Paragraph paragraph1 = document.addParagraph("Программа валидации");
            paragraph1.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Font myFont = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 14, Color.BLACK);
            paragraph1.setFont(myFont);
            Paragraph paragraph11 = document.addParagraph("");

            Font myFont2 = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 12, Color.BLACK);

            Table table1 = document.addTable(3, 1);
            Cell cell1 = table1.getCellByPosition(0, 0);
            cell1.setStringValue("Программа");
            cell1.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            cell1.setFont(myFont2);
            Cell cell2 = table1.getCellByPosition(0, 1);
            cell2.setStringValue(program);
            Cell cell3 = table1.getCellByPosition(0, 2);
            cell3.setStringValue("Материальная база");
            cell3.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            cell3.setFont(myFont2);
           int num1=info.tablerows1.size();

            Table table2 = document.addTable(1+num1, 3);
            Cell cell4 = table2.getCellByPosition(0, 0);
            cell4.setStringValue("Название, марка, модель оборудования");
            Cell cell5 = table2.getCellByPosition(1, 0);
            cell5.setStringValue("Количество оборудования");
            Cell cell6 = table2.getCellByPosition(2, 0);
            cell6.setStringValue("Технические характеристики");

            for(int i=0; i<info.tablerows1.size(); i++) {
                table2.getCellByPosition(0, i+1).setStringValue(i + 1 + "." + info.tablerows1.get(i).businessobject);
                table2.getCellByPosition(1, i+1).setStringValue(info.tablerows1.get(i).numberequipment);
                table2.getCellByPosition(2, i+1).setStringValue(info.tablerows1.get(i).specification);
            }

            Table table3 = document.addTable(1, 1);
            Cell cell7 = table3.getCellByPosition(0, 0);
            cell7.setStringValue("Параметр(ы)");
            cell7.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            cell7.setFont(myFont2);

            int num2=info.tablerows2.size();

            Table table4 = document.addTable(1+num2, 2);
            Cell cell8 = table4.getCellByPosition(0, 0);
            cell8.setStringValue("Название параметра");
            cell8.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Cell cell9 = table4.getCellByPosition(1, 0);
            cell9.setStringValue("Описание методики испытания и последующей оценки полученных результатов");
            cell9.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);

            for(int j=0; j<info.tablerows2.size(); j++) {
                table4.getCellByPosition(0, j+1).setStringValue(j + 1 + "." + info.tablerows2.get(j).titleparametr);
                table4.getCellByPosition(1, j+1).setStringValue(info.tablerows2.get(j).descriptionmethodic);
            }


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

            document.save("/home/anton/printForms/validation.odt");

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
