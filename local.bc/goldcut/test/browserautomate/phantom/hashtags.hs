import System.Process
import Data.List
import Control.Concurrent
import Control.Monad
import System.IO

--hashTags :: String -> MVar-> IO()
hashTags dir m = do
 let x = readProcess "phantomjs" ["--ssl-protocol=any","hashtags.js", dir] []
 -- replicateM_ 10 (putChar 'A')
 -- let x = readProcess "ls" ["-la", dir] []
 y <- x
 putStrLn y
 --mapM_ print $ sortBy sortGT $ count y
 --mapM_ print y
 putMVar m "123456"

count :: String -> [(String,Int)]
count xs = filter ((>=5).snd) $
     map(\ws -> (head ws, length ws)) $
           group $ sort $ words xs

sortGT :: (Ord a, Ord a1) => (a1, a) -> (a1, a) -> Ordering
sortGT (a1, b1) (a2, b2)
  | b1 < b2 = GT
  | b1 > b2 = LT
  | b1 == b2 = compare a1 a2

main = do
  hSetBuffering stdout NoBuffering
  m <- newEmptyMVar
  m2 <- newEmptyMVar
  -- forkIO $ replicateM_ 10 (putChar 'B')
  forkIO $ hashTags "haskell" m
  forkIO $ hashTags "ruby" m2
  r <- takeMVar m
  r2 <- takeMVar m2
  print r
  print r2
  putStrLn "END"
