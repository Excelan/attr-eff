package experiments.feature;

public class Files {

    // http://blog.jooq.org/2014/01/10/java-8-friday-goodies-java-io-finally-rocks/

    // // http://docs.oracle.com/javase/tutorial/essential/io/find.html

    /**
     * Path path= Paths.get("src/main/resources/process");
     final List<Path> files = new ArrayList<>();
     try {
     Files.walkFileTree(path, new SimpleFileVisitor<Path>(){
    @Override
    public FileVisitResult visitFile(Path file, BasicFileAttributes attrs) throws IOException {
    if(!attrs.isDirectory()){
    files.add(file);
    }
    return FileVisitResult.CONTINUE;
    }
    });
     } catch (IOException e) {
     e.printStackTrace();
     }
     */

}
