Feature: System Info
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor volumes in system information

  @acceptance-back
  Scenario: Monitor the number of channels
    Given a catalog with 3 channels
    When the administrator user asks for the system information
    Then the system information returns that the number of channels is 3

  @acceptance-back
  Scenario: Monitor the number of locales
    Given a catalog with 6 locales
    When the administrator user asks for the system information
    Then the system information returns that the number of locales is 6

  @acceptance-back
  Scenario: Monitor the number of products
    Given a catalog with 10 products
    When the administrator user asks for the system information
    Then the system information returns that the number of products is 10

  @acceptance-back
  Scenario: Monitor the number of product models
    Given a catalog with 8 product models
    When the administrator user asks for the system information
    Then the system information returns that the number of product models is 8

  @acceptance-back
  Scenario: Monitor the number of variant products
    Given a catalog with 5 variant products
    When the administrator user asks for the system information
    Then the system information returns that the number of variant products is 5

  @acceptance-back
  Scenario: Monitor the number of families
    Given a catalog with 7 families
    When the administrator user asks for the system information
    Then the system information returns that the number of families is 7

  @acceptance-back
  Scenario: Monitor the number of users
    Given a catalog with 22 users
    When the administrator user asks for the system information
    Then the system information returns that the number of users is 22

  @acceptance-back
  Scenario: Monitor the number of categories
    Given a catalog with 5 categories
    When the administrator user asks for the system information
    Then the system information returns that the number of categories is 5

  @acceptance-back
  Scenario: Monitor the number of category trees
    Given a catalog with 7 category trees
    When the administrator user asks for the system information
    Then the system information returns that the number of category trees is 7

  @acceptance-back
  Scenario: Monitor the max of category in one category
    Given a catalog with 8 category in one category
    When the administrator user asks for the system information
    Then the system information returns that the maximum of category in one category is 8

  @acceptance-back
  Scenario: Monitor the max of category levels
    Given a catalog with 12 category levels
    When the administrator user asks for the system information
    Then the system information returns that the maximum of category levels is 12

  @acceptance-back
  Scenario: Monitor the number of product values
    Given a catalog with 487520 product values
    When the administrator user asks for the system information
    Then the system information returns that the number of product values is 487520

  @acceptance-back
  Scenario: Monitor the average of product values by product
    Given a product with 587 product values
    And a product model with 565 product values
    When the administrator user asks for the system information
    Then the system information returns that the average of product values by product is 576
