<?php

  namespace Sliver\TestCondition;
  use Sliver\Utility\String;
  
  class ArrayTestConditionFactory extends TestConditionFactory {
    
    // Strict equality for array means exact order. Relax it for arrays.
    public function equalTo ( $other ) {
      $value = $this->value;
      $this->test->addCondition( new TestCondition(
        String::serialize( $value ) . ' == ' . String::serialize( $other ),
        function ( $result ) use ( $value, $other ) {
          return $value == $other;
        }
      ));
      return $this;
    }

    public function contains ( $other ) {
      $value = $this->value;
      $this->test->addCondition( new TestCondition(
        'array contains value ' . String::serialize( $other ),
        function ( $result ) use ( $value, $other ) {
          return in_array( $other, $value );
        }
      ));
      return $this;
    }
    
    public function hasValue ( $other ) {
      return $this->contains( $other );
    }
    
    public function has ( $other ) {
      return $this->contains( $other );
    }
    
    public function doesNotContain ( $other ) {
      $value = $this->value;
      $this->test->addCondition( new TestCondition(
        'array does not contain value ' . String::serialize( $other ),
        function ( $result ) use ( $value, $other ) {
          return !in_array( $other, $value );
        }
      ));
      return $this;
    }
    
    public function hasNot ( $other ) {
      return $this->doesNotContain( $other );
    }
    
    public function hasKey ( $key ) {
      $value = $this->value;
      $this->test->addCondition( new TestCondition(
        'array contains key ' . String::serialize( $key ),
        function ( $result ) use ( $value, $key ) {
          return array_key_exists( $key, $value );
        }
      ));
      return $this;
    }
    
    public function isSize ( $size ) {
      $value = $this->value;
      $this->test->addCondition( new TestCondition(
        'array is size ' . String::serialize( $size ),
        function ( $result ) use ( $value, $size ) {
          return sizeof( $value ) === $size;
        }
      ));
      return $this;
    }
    
    public function size ( $size ) {
      return $this->isSize( $size );
    }
    
    public function isEmpty () {
      return $this->isSize( 0 );
    }
    
    public function len ( $len ) {
        return $this->isSize( $len );
    }
    
    public function length ( $len ) {
      return $this->isSize( $len );
    }

  };

?>