package net.goldcut.utils;

import com.google.zxing.BarcodeFormat;
import com.google.zxing.Writer;
import com.google.zxing.client.j2se.MatrixToImageWriter;
import com.google.zxing.common.BitMatrix;
import com.google.zxing.oned.Code128Writer;
import com.google.zxing.pdf417.PDF417Writer;
import com.google.zxing.pdf417.encoder.PDF417;
import com.google.zxing.qrcode.QRCodeWriter;
import java.io.File;
import java.io.FileOutputStream;


import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.util.HashMap;
import java.util.Map;

import javax.imageio.ImageIO;

import com.google.zxing.BarcodeFormat;
import com.google.zxing.BinaryBitmap;
import com.google.zxing.EncodeHintType;
import com.google.zxing.MultiFormatReader;
import com.google.zxing.MultiFormatWriter;
import com.google.zxing.NotFoundException;
import com.google.zxing.Result;
import com.google.zxing.WriterException;
import com.google.zxing.client.j2se.BufferedImageLuminanceSource;
import com.google.zxing.client.j2se.MatrixToImageWriter;
import com.google.zxing.common.BitMatrix;
import com.google.zxing.common.HybridBinarizer;
import com.google.zxing.qrcode.decoder.ErrorCorrectionLevel;


public class BarCodeGenerator {

    public static void generateCode128(Integer code) {

        BitMatrix bitMatrix;

        try {
            //  Write Barcode
            bitMatrix = new Code128Writer().encode(code.toString(), BarcodeFormat.CODE_128, 130, 80, null);
            MatrixToImageWriter.writeToStream(bitMatrix, "png", new FileOutputStream(new File("/Users/max/Sites/bc/tmp/code128_"+code+".png")));
            //System.out.println("Code128 Barcode Generated.");
            /*
            //  Write QR Code
            Writer writer = new QRCodeWriter();
            bitMatrix = writer.encode("123456789", BarcodeFormat.QR_CODE, 200, 200);
            MatrixToImageWriter.writeToStream(bitMatrix, "png", new FileOutputStream(new File("/Users/max/Sites/bc/2qr.png")));
            System.out.println("QR Code Generated.");
            //  Write PDF417
            writer = new PDF417Writer();
            bitMatrix = writer.encode("123456789 test", BarcodeFormat.PDF_417, 300, 120);
            MatrixToImageWriter.writeToStream(bitMatrix, "png", new FileOutputStream(new File("/Users/max/Sites/bc/3pdf417.png")));
            System.out.println("PDF417 Code Generated.");
            */
        } catch (Exception e) {
            System.out.println("Barcode generation Exception " + e.getMessage());
        }

    }

}
