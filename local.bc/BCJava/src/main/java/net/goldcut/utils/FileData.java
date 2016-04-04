package net.goldcut.utils;

import java.io.IOException;
import java.nio.charset.Charset;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Paths;

public class FileData {

    public static String readFile(String path) throws IOException
    {
        Charset encoding = StandardCharsets.UTF_8;
        byte[] encoded = Files.readAllBytes(Paths.get(path));
        return new String(encoded, encoding);
    }
}