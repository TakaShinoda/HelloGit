#include <stdio.h>



void FizzBuzz(int i){
  if (i % 3 == 0 && i % 5 == 0) {
    printf("FizzBuzz\n");
  } else if (i % 3 == 0) {
    printf("Fizz\n");
  } else if (i % 5 == 0) {
    printf("Buzz\n");
  } else {
    printf("%d\n", i);
  }
  //  return 0;
}

int main(void) {
  int a;
    for (a = 1; a <= 100; a++) {
      FizzBuzz(a);
    }
}
