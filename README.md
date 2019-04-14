### Use case

I wanted to list all tests I have in my project testing specific namespace

### What 

This is a simple tool that does one thing in two steps 
-  Reads all php files in the given path
- list only the files that contain the given namespace


### How to use

 ```
 ./PHPTestsCalls -p ./Tests/ -s 'Symfony\Component\Finder\Comparator'
Comparator/DateComparatorTest.php
 * PHPUnit\Framework\TestCase
 * Symfony\Component\Finder\Comparator\DateComparator

Comparator/ComparatorTest.php
 * PHPUnit\Framework\TestCase
 * Symfony\Component\Finder\Comparator\Comparator

Comparator/NumberComparatorTest.php
 * PHPUnit\Framework\TestCase
 * Symfony\Component\Finder\Comparator\NumberComparator

Iterator/DateRangeFilterIteratorTest.php
 * Symfony\Component\Finder\Comparator\DateComparator
 * Symfony\Component\Finder\Iterator\DateRangeFilterIterator

Iterator/SizeRangeFilterIteratorTest.php
 * Symfony\Component\Finder\Comparator\NumberComparator
 * Symfony\Component\Finder\Iterator\SizeRangeFilterIterator
```