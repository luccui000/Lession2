create table danhmuc (
     id int primary key not null,
     ten_dm varchar(50) not null
)

CREATE TABLE sanpham (
     id int primary key not null,
     ten varchar(200) not null,
     hinh_anh varchar(400),
     danhmuc_id int references danhmuc(id)
)