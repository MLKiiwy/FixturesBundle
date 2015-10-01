Feature: I load a simple fixture without dependencies

    Scenario:
        Given I enable the profiler
        And I have data in "default" database with:
            |@TestBundle/Resources/fixtures/default/dummy|
        Then table "dummy" on connection "default" should contain 10 records
        When I execute the SQL query "SELECT * FROM dummy WHERE id = 1" on "default"
        Then I have 1 query row result
        And the SQL query result row 1 should contain values:
        """
        {
            "name": "Dummy1"
        }
        """
