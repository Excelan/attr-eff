package experiments.feature;

// http://www.mkyong.com/java8/java-8-foreach-examples/
// http://www.sergiy.ca/how-to-iterate-over-a-map-in-java/
// http://java67.blogspot.com/2014/05/3-examples-to-loop-map-in-java-foreach.html
// http://habrahabr.ru/post/128269/
// http://tutorials.jenkov.com/java-collections/list.html

// filter
// http://stackoverflow.com/questions/29490418/iterating-and-filtering-two-lists-using-java-8
// JS! https://github.com/winterbe/streamjs
// AVERAGE https://docs.oracle.com/javase/tutorial/collections/streams/reduction.html

// availableProcessMastercopies = new HashMap<String, ManagedProcessMastercopy>();
/**
 * for (Map.Entry<String, String> entry : map.entrySet())
 {
 System.out.println(entry.getKey() + "/" + entry.getValue());
 }
 items.put("F", 60);
 for (Map.Entry<String, Integer> entry : items.entrySet()) {
 System.out.println("Item : " + entry.getKey() + " Count : " + entry.getValue());
 }
 items.forEach((k,v)->System.out.println("Item : " + k + " Count : " + v));
 items.forEach((k,v)->{
 System.out.println("Item : " + k + " Count : " + v);
 if("E".equals(k)){
 System.out.println("Hello E");
 }
 });
 */


/*

//files.forEach(item -> buildPrototypesFromXMLXpec(db, filePath));
private void safeFoo(final A a) {
    try {
        a.foo();
    } catch (Exception ex) {
        throw new RuntimeException(ex);
    }
}
(Supertype exception Exception is only used as example, never try to catch it yourself)
Then you can call it with: as.forEach(this::safeFoo)
 */

public class Collections {
}
