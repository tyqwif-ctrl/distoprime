<?php
// index.php - Главная страница
session_start();
include 'db_connect.php'; 

// === 1. PHP-логика: Загрузка данных и определение статуса админа ===
$sql = "SELECT id, title, description, city, image_url, working_hours, website, short_description 
        FROM attractions 
        ORDER BY title";

$attractions = [];
$result = null; // Инициализация для избежания "Undefined variable"

try {
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $attractions[] = $row;
        }
    }
} catch (mysqli_sql_exception $e) {
    // В случае ошибки SQL, $attractions останется пустым, и это безопасно
}

if (isset($conn)) {
    $conn->close();
}

// *** КРИТИЧЕСКИ ВАЖНОЕ: Проверка роли ***
// Переменная для React: статус администратора (true или false). Выводится без кавычек.
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'true' : 'false'; 

// Преобразуем данные в JSON для передачи в React
$attractions_json = json_encode($attractions, JSON_UNESCAPED_UNICODE);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Достопримечательности мира</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo-container">
        <a href="index.php">
            <img src="images/logo.svg" alt="" class="site-logo">
            <span class="site-title">Достопримечательности</span> 
        </a>
    </div>
        
        <div class="user-status">
            <?php if (isset($_SESSION['username'])): ?>
                <p>Привет, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
                <a href="logout.php" class="button button-logout">Выйти</a>
            <?php else: ?>
                <a href="login.php" class="button button-login">Войти</a> 
                <a href="register.php" class="button button-register">Регистрация</a>
            <?php endif; ?>
        </div>
    </header>
    
    <div id="react-root">
        <div class="loading-message">Загрузка данных...</div>
    </div>

    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

    <script type="text/babel">
        const { useState, useEffect } = React;
        const INITIAL_ATTRACTIONS = <?php echo $attractions_json; ?>;
        // 🔥 ПЕРЕДАЧА СТАТУСА АДМИНА В БУЛЕВОМ ФОРМАТЕ
        const IS_ADMIN = <?php echo $is_admin; ?>; 

        // =================================================================
        // 1. КОМПОНЕНТ ДЕТАЛЬНОГО ПРОСМОТРА (МОДАЛЬНОЕ ОКНО)
        // =================================================================
        const AttractionDetail = ({ attraction, onClose }) => {
            if (!attraction) return null; 

            const handleContentClick = (e) => e.stopPropagation();

            return (
                <div className="modal-backdrop" onClick={onClose} style={{zIndex: 1000}}>
                    <div className="modal-content" onClick={handleContentClick} style={{maxWidth: '800px'}}>
                        <button className="close-button" onClick={onClose}>&times;</button>
                        <h2>{attraction.title}</h2>
                        <div className="modal-image-wrapper">
                            <img 
                                src={`images/${attraction.image_url || 'default.jpg'}`} 
                                alt={attraction.title} 
                            />
                        </div>
                        
                        <div className="modal-body">
                            <p><strong>Город:</strong> {attraction.city}</p>
                            <p><strong>Описание:</strong> {attraction.description}</p>
                            
                            {attraction.working_hours && 
                                <p><strong>Время работы:</strong> {attraction.working_hours}</p>
                            }
                            
                            {attraction.website && 
                                <p><strong>Сайт:</strong> <a href={attraction.website} target="_blank" rel="noopener noreferrer">{attraction.website}</a></p>
                            }
                        </div>
                    </div>
                </div>
            );
        };

        // =================================================================
        // 2. КОМПОНЕНТ ФОРМЫ (CREATE/UPDATE)
        // =================================================================
        const AttractionForm = ({ initialData, onClose, setAttractions }) => {
            const isEditing = initialData !== null && initialData.id;
            const [formData, setFormData] = useState(initialData || {
                title: '',
                description: '',
                city: '',
                short_description: '',
                image_url: '',
                working_hours: '',
                website: ''
            });

            const handleChange = (e) => {
                const { name, value } = e.target;
                setFormData(prev => ({ ...prev, [name]: value }));
            };
            
            const handleSubmit = async (e) => {
                e.preventDefault();
                
                const method = isEditing ? 'PUT' : 'POST';
                const url = isEditing ? 'update_attraction.php' : 'create_attraction.php';
                
                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json', 
                        },
                        body: JSON.stringify(formData)
                    });
                    
                    const result = await response.json();

                    if (result.success) {
                        alert(isEditing ? 'Обновлено успешно!' : 'Добавлено успешно!');
                        
                        setAttractions(prev => {
                            const newAttractionData = { ...formData, id: result.id || formData.id };
                            
                            if (isEditing) {
                                // UPDATE: Заменяем старый объект на обновленный
                                return prev.map(a => 
                                    a.id === newAttractionData.id ? newAttractionData : a
                                );
                            } else {
                                // CREATE: Добавляем новый объект с ID, полученным из БД
                                return [...prev, newAttractionData];
                            }
                        });
                        
                        onClose();
                    } else {
                        alert(`Ошибка: ${result.message}`);
                    }

                } catch (error) {
                    console.error('Ошибка сети:', error);
                    alert('Произошла сетевая ошибка при сохранении.');
                }
            };

            return (
                <div className="modal-backdrop" onClick={onClose} style={{ zIndex: 1100 }}>
                    <div className="modal-content" onClick={(e) => e.stopPropagation()} style={{ maxWidth: '800px' }}>
                        <button className="close-button" onClick={onClose}>&times;</button>
                        <h2>{isEditing ? 'Редактирование: ' + (initialData?.title || 'Объект') : 'Добавить новую'}</h2>

                        <form onSubmit={handleSubmit} className="attraction-form">
                            <label>Название:</label>
                            <input name="title" value={formData.title} onChange={handleChange} required />
                            
                            <label>Город:</label>
                            <input name="city" value={formData.city} onChange={handleChange} required />
                            
                            <label>Описание (Полное):</label>
                            <textarea name="description" value={formData.description} onChange={handleChange} required></textarea>
                            
                            <label>Краткое описание (для карточки):</label>
                            <input name="short_description" value={formData.short_description || ''} onChange={handleChange} />

                            <label>Имя файла изображения (например, acropolis.jpg):</label>
                            <input name="image_url" value={formData.image_url || ''} onChange={handleChange} />
                            
                            <label>Время работы:</label>
                            <input name="working_hours" value={formData.working_hours || ''} onChange={handleChange} />
                            
                            <label>Сайт (URL):</label>
                            <input name="website" value={formData.website || ''} onChange={handleChange} />
                            
                            <button type="submit" className="button button-save">
                                {isEditing ? 'Сохранить изменения' : 'Создать'}
                            </button>
                        </form>
                    </div>
                </div>
            );
        };

        // =================================================================
        // 3. КОМПОНЕНТ КАРТОЧКИ (С КЛИКОМ И АДМИН-КНОПКАМИ)
        // =================================================================
        const AttractionCard = ({ attraction, onCardClick, onDelete, onEdit, isAdmin }) => ( 
            <div 
                className="attraction-card" 
                data-city={attraction.city} 
                onClick={() => onCardClick(attraction)}
            >
                <img 
                    src={`images/${attraction.image_url || 'default.jpg'}`} 
                    alt={attraction.title} 
                />
                <div className="card-content">
                    <h2>{attraction.title}</h2>
                    <p className="city">Город: <strong>{attraction.city}</strong></p>
                    <p>{attraction.short_description || (attraction.description ? attraction.description.substring(0, 100) + '...' : 'Нет описания.')}</p> 
                </div>
                
                <div className="card-footer" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <button className="button-details" style={{ flexGrow: 1, marginRight: isAdmin ? '10px' : '0' }}>Подробнее</button>
                    
                    {/* Кнопки администратора: показываются только если isAdmin = true */}
                    {isAdmin && (
                        <div style={{ display: 'flex', gap: '5px' }}>
                            <button 
                                className="button-edit" 
                                onClick={(e) => {
                                    e.stopPropagation();
                                    onEdit(attraction);
                                }}
                                title="Редактировать"
                            >
                                ✏️
                            </button>
                            <button 
                                className="button-delete" 
                                onClick={(e) => {
                                    e.stopPropagation();
                                    onDelete(attraction.id);
                                }}
                                title="Удалить"
                            >
                                🗑️
                            </button>
                        </div>
                    )}
                </div>
            </div>
        );

        // =================================================================
        // 4. ГЛАВНЫЙ КОМПОНЕНТ ПРИЛОЖЕНИЯ
        // =================================================================
        const AttractionsApp = () => {
            const [attractions, setAttractions] = useState(INITIAL_ATTRACTIONS); 
            const [searchTerm, setSearchTerm] = useState('');
            const [selectedAttraction, setSelectedAttraction] = useState(null); 
            const [isFormVisible, setIsFormVisible] = useState(false); 
            const [editingAttraction, setEditingAttraction] = useState(null); 
            
            // DELETE
            const deleteAttraction = async (id) => {
                if (!window.confirm("Вы уверены, что хотите удалить эту достопримечательность?")) {
                    return;
                }
                
                try {
                    const response = await fetch('delete_attraction.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${id}` 
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        setAttractions(prevAttractions => 
                            prevAttractions.filter(a => a.id !== id)
                        );
                        alert('Удалено!');
                    } else {
                        alert('Ошибка удаления: ' + result.message);
                    }
                } catch (error) {
                    console.error('Ошибка сети:', error);
                    alert('Произошла сетевая ошибка.');
                }
            };
            
            // EDIT/UPDATE
            const handleEditClick = (attraction) => {
                setEditingAttraction(attraction);
                setIsFormVisible(true);
            };

            const handleCardClick = (attraction) => {
                setSelectedAttraction(attraction);
            };

            const handleCloseDetail = () => {
                setSelectedAttraction(null);
            };
            
            const handleCloseForm = () => {
                setIsFormVisible(false);
                setEditingAttraction(null);
            }

            const filteredAttractions = attractions.filter(attraction => {
                const search = searchTerm.toLowerCase();
                const title = (attraction.title || '').toLowerCase(); 
                const city = (attraction.city || '').toLowerCase();

                return title.includes(search) || city.includes(search);
            });

            const handleSearchChange = (event) => {
                setSearchTerm(event.target.value);
            };

            return (
                <>
                    <div className="search-box">
                        <input 
                            type="text" 
                            id="searchInput" 
                            placeholder="Поиск по названию или городу..." 
                            value={searchTerm}
                            onChange={handleSearchChange}
                        />
                    </div>
                    
                    {/* Кнопка "Добавить" видна только администраторам */}
                    {IS_ADMIN && (
                        <div className="admin-actions">
                            <button 
                                className="button button-add-new"
                                onClick={() => {
                                    setEditingAttraction(null); // Сброс для режима "Создать"
                                    setIsFormVisible(true);
                                }}
                            >
                                + Добавить новую достопримечательность
                            </button>
                        </div>
                    )}
                    
                    {/* Форма добавления/редактирования */}
                    {isFormVisible && (
                        <AttractionForm 
                            initialData={editingAttraction}
                            onClose={handleCloseForm}
                            setAttractions={setAttractions} 
                        />
                    )}

                    <main className="attractions-list">
                        {filteredAttractions.length > 0 ? (
                            filteredAttractions.map(attraction => (
                                <AttractionCard 
                                    key={attraction.id} 
                                    attraction={attraction} 
                                    onCardClick={handleCardClick}
                                    onDelete={deleteAttraction} 
                                    onEdit={handleEditClick}    
                                    isAdmin={IS_ADMIN}          
                                />
                            ))
                        ) : (
                            <p style={{ textAlign: 'center', width: '100%', padding: '50px' }}>
                                Достопримечательности по вашему запросу не найдены.
                            </p>
                        )}
                    </main>

                    {/* РЕНДЕРИНГ МОДАЛЬНОГО ОКНА ДЕТАЛЕЙ */}
                    {selectedAttraction && (
                        <AttractionDetail 
                            attraction={selectedAttraction} 
                            onClose={handleCloseDetail} 
                        />
                    )}
                </>
            );
        };

        const rootContainer = document.getElementById('react-root');
        ReactDOM.createRoot(rootContainer).render(<AttractionsApp />);
    </script>
</body>
</html>