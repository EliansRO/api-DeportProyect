<?php
use PHPUnit\Framework\TestCase;
use App\Config\Database;
use App\Models\ItemModel;
use PDO;

class ItemModelTest extends TestCase {
    private $model;

    protected function setUp(): void {
        $db = (new Database())->getConnection();
        $this->model = new ItemModel($db);
    }

    public function testConnection(): void {
        $this->assertInstanceOf(PDO::class, (new Database())->getConnection());
    }

    public function testGetAllReturnsStatement(): void {
        $stmt = $this->model->getAll();
        $this->assertInstanceOf(PDOStatement::class, $stmt);
    }
}
