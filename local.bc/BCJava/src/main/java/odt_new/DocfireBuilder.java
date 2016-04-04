package odt_new;

import org.odftoolkit.odfdom.type.Color;
import org.odftoolkit.simple.TextDocument;
import org.odftoolkit.simple.style.Font;
import org.odftoolkit.simple.style.StyleTypeDefinitions;
import org.odftoolkit.simple.text.Paragraph;

/**
 * Created by Iryna on 04.01.2016.
 */
public class DocfireBuilder {
    public static void main(String[] args) {
        try {
            TextDocument document =TextDocument.newTextDocument();

            DocfireInfo info = new DocfireInfo();
            String enterprise=info.enterprise;
            String masterpart=info.masterpart;
            String based =info.based;
            String date=info.date;

            String director = info.director;

            Paragraph paragraph00=document.addParagraph("Директору");
            Paragraph paragraph01=document.addParagraph(enterprise);
            Paragraph paragraph02=document.addParagraph(director);

            paragraph00.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.RIGHT);
            paragraph01.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.RIGHT);
            paragraph02.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.RIGHT);

            document.addParagraph("");
            document.addParagraph("");

            Paragraph paragraph1 = document.addParagraph("Заявление");
            paragraph1.setHorizontalAlignment(StyleTypeDefinitions.HorizontalAlignmentType.CENTER);
            Font myFont = new Font("Arial", StyleTypeDefinitions.FontStyle.BOLD, 14, Color.BLACK);
            paragraph1.setFont(myFont);
            document.addParagraph("");

            Paragraph paragraph2 = document.addParagraph(masterpart+" "+based+".");

            for (int i=0;i<38;i++) {
                document.addParagraph("");
            }

            Paragraph paragraph14 = document.addParagraph(date+"                                                                            "+"___________________");


            document.save("/home/anton/printForms/docfire.odt");

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
