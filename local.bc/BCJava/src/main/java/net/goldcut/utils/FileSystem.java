package net.goldcut.utils;


import java.io.IOException;

public class FileSystem {

    public static String basedir() {
        String current = "/tmp";
        try {
            current = (new java.io.File(".").getCanonicalPath()) + "/..";
            //String currentDir = System.getProperty("user.dir");
        } catch (IOException e) {
            e.printStackTrace();
        }
        return current;
    }

}
